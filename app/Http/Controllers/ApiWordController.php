<?php

namespace App\Http\Controllers;

use App\Models\ExamBank;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamResultDetail;
use App\Models\ExamShiftDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\IOFactory as PHPIOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use App\Enums\FileType;
use Illuminate\Http\Response;
use ZipArchive;
use App\Models\Criteria;
use App\Enums\PropertyType;
use App\Enums\InfoType;
use App\Enums\PageSize;
use App\Enums\StyleType;
use App\Enums\FontType;
use App\Enums\FooterType;
use App\Enums\NumberingType;
use App\Enums\FontColor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use function Symfony\Component\String\b;

class ApiWordController extends Controller
{

    public $_PATH_EXTRACTED = 'app/extracted';
    public $_PATH_ZIP = 'app/temp';
    private const CAKE_LIST_NAME = 'cache_list';

    private $_SEPARATOR = 'ーー';
    private const CAKE_STUDENT_NAME = 'cache_student';
    private $paragraphWithout = []; // các đoạn văn bản bỏ qua khi check áp dụng văn bản toàn bộ

    private const START_ROW = 1;

    public function __construct()
    {
        $this->_PATH_EXTRACTED = public_path($this->_PATH_EXTRACTED);
        $this->_PATH_ZIP = public_path($this->_PATH_ZIP);
    }

    /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function index()
    {
        $examBanks = Exam::with('examShifts.departments', 'examShifts.examBanks.criterias', 'examShifts.examShiftDetails')->get();
        if ($examBanks) {
            return $this->sendResponseSuccess($examBanks->toArray());
        }
        return $this->sendResponseSuccess([]);
    }

    public function uploadFile(Request $request)
    {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        if (!$receiver->isUploaded()) {
            return $this->sendResponseError(['message' => 'Lỗi tải file']);
        }
        $fileReceived = $receiver->receive(); // receive file
        if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
            $subjectLine = $request->subjectLine;
            $fileType = $request->fileType;
            $listExamBank = $request->examBanks ? json_decode($request->examBanks, true) : [];
            $countExam = count($listExamBank);
            $cakeListKey = self::CAKE_LIST_NAME . '-' . $request->examId . '-' . $request->examShiftId . '-' . $request->departmentId;
            $cakeStudentKey = self::CAKE_STUDENT_NAME . '-' . $request->examId . '-' . $request->examShiftId . '-' . $request->departmentId;
            if ($fileType == FileType::EXAM || $fileType == FileType::LIST) {
                $subPath = "/{$request->examId}{$request->examShiftId}{$request->departmentId}";
                $file = $fileReceived->getFile(); // get file
                // file danh sách
                if ($fileType == FileType::LIST) {
                    $spreadsheet = IOFactory::load($file);
                    $sheet = $spreadsheet->getSheet($request->sheetIndex);
                    // Lấy dữ liệu từ các ô trong sheet
                    $data = [];
                    foreach ($sheet->getRowIterator() as $index => $row) {
                        if ($index > $subjectLine - 1) {
                            $candidateNumber = $sheet->getCell("A{$index}")->getValue();
                            $studentCode = $sheet->getCell("B{$index}")->getValue();
                            $studentName = $sheet->getCell("C{$index}")->getValue();
                            if ($studentCode && $studentName && $candidateNumber && (int)$candidateNumber > 0) {
                                $index = ($candidateNumber - 1) % $countExam;
                                $data[$studentCode] = [
                                    'candidateNumber' => $candidateNumber,
                                    'studentCode' => $studentCode,
                                    'studentName' => $studentName,
                                    'departmentName' => $request->department_name,
                                    'examBankName' => $listExamBank[$index]['exam_bank_name'],
                                    'examBankId' => $listExamBank[$index]['id'],
                                ];
                            }
                        }
                    }
                    Cache::store('file')->put($cakeListKey, $data, now()->addDay());
                    if (empty($data)) {
                        return $this->sendResponseSuccess([]);
                    }
                    return $this->sendResponseSuccess([
                        'cakeListName' => self::CAKE_LIST_NAME,
                    ]);
                } else {
                    // folder lưu file tải lên
                    //xóa các file trước đó
                    $listData = Cache::get($cakeListKey);
                    // Di chuyển tệp đã tải lên vào thư mục tạm
                    File::deleteDirectory($this->_PATH_ZIP . $subPath);
                    File::deleteDirectory($this->_PATH_EXTRACTED . $subPath);
                    mkdir($this->_PATH_ZIP . $subPath, 0777, true);
                    mkdir($this->_PATH_EXTRACTED . $subPath, 0777, true);
                    $filePath = $file->move($this->_PATH_ZIP . $subPath, $file->getClientOriginalName());
                    $valid_docx = array('docx');
                    $extractPath = $this->_PATH_EXTRACTED . $subPath;
                    $zip = new ZipArchive;
                    $res = $zip->open($filePath);
                    if ($res === TRUE) {
                        // Extract file
                        $zip->extractTo($extractPath);
                        $zip->close();
                    } else {
                        return $this->sendResponseError(['message' => 'Lỗi giải nén']);
                    }

                    $handleParent = opendir($extractPath);
                    $students = [];
                    $pathTo = "P";
                    if ($handleParent) {
                        //đọc folder bài tải lên
                        while (($fileParent = readdir($handleParent)) !== FALSE) {
                            if (!in_array($fileParent, array('.', '..')) && !is_dir($extractPath . $fileParent)) {
                                //đọc từng folder con để lấy ra bài word | excel | pp tương ứng
                                $dirChild = $extractPath . '/' . $fileParent;
                                $parts = explode('_', $fileParent);
                                $studentID = $parts[0];                                       //mã sinh viên
                                if (is_dir($dirChild) && $handleChild = opendir($dirChild)) {
                                    while (($fileChild = readdir($handleChild)) !== FALSE) {
                                        if (!in_array($fileChild, array('.', '..')) && !is_dir($dirChild . $fileChild)) {
                                            $ext = strtolower(pathinfo($fileChild, PATHINFO_EXTENSION));
                                            if ($pathTo === "P") {
                                                if (in_array(strtolower($ext), $valid_docx) && array_key_exists($studentID, $listData)) {
                                                    //thông tin sinh viên
                                                    $students['data'][$listData[$studentID]['candidateNumber']] = [
                                                        'studentName' => $listData[$studentID]['studentName'],
                                                        'studentID' => $studentID,
                                                        'candidateNumber' => $listData[$studentID]['candidateNumber'],
                                                        'examBankId' => $listData[$studentID]['examBankId'],
                                                        'examBankName' => $listData[$studentID]['examBankName'],
                                                        'departmentName' => $request->departmentName,
                                                        'studentAssignment' => [$fileChild],
                                                        'style' => $this->getListStyles($extractPath . '/' . $fileParent . '/' . $fileChild),
                                                        'path' => $extractPath . '/' . $fileParent,
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                    closedir($handleChild);
                                }
                            }
                        }
                        closedir($handleParent);
                    }
                }

                if (empty($students)) {
                    return $this->sendResponseSuccess();
                }
                $students['files'] = [
                    'extractPath' => $extractPath,
                ];
                $students['cakeStudentName'] = self::CAKE_STUDENT_NAME . '-' . $request->examId . '-' . $request->examShiftId . '-' . $request->departmentId;
                $students['cakeListName'] = self::CAKE_LIST_NAME . '-' . $request->examId . '-' . $request->examShiftId . '-' . $request->departmentId;
                Cache::store('file')->put($cakeStudentKey, $students, now()->addDay());
                return $this->sendResponseSuccess($students);
            } else {
                return $this->sendResponseError(['message' => 'Đã xảy ra lỗi']);
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function calculate(Request $request)
    {
        $list = Cache::get($request->cakeListName);
        $listStudent = Cache::get($request->cakeStudentName) ? Cache::get($request->cakeStudentName)['data'] : [];
        $listExamBank = $request->listExamBank;
        $listExamShiftDetail = $request->examShift['exam_shift_details'];
        $examShiftName = $request->examShift['exam_shift_name'];
        if (empty($list) || empty($listStudent) || empty($listExamBank) || empty($listExamShiftDetail)) {
            return $this->sendResponseError(['message' => 'Đã xảy ra lỗi']);
        }
        $listExamShiftDetail = collect($listExamShiftDetail)->keyBy('exam_bank_id')->toArray();
        $listExamBank = collect($listExamBank)->keyBy('id')->toArray();
        $result = [];
        $listCandidateNumber = [];
        try {
            foreach ($listExamBank as $exam) {
                DB::beginTransaction();
                $exam_shift_detail_id = $listExamShiftDetail[$exam['id']]['id'];
                ExamResult::where('exam_shift_detail_id', $exam_shift_detail_id)->delete();
                ExamResultDetail::where('exam_shift_detail_id', $exam_shift_detail_id)->delete();
                $this->calculateByExam($result, $listStudent, $exam, $request->department, $listExamShiftDetail, $listExamBank, $examShiftName, $listCandidateNumber);
                DB::commit();
                return $this->sendResponseSuccess($result);
            }
        } catch (\Throwable $th) {
            dd($th);
            return $this->sendResponseError();
        }
    }

    /**
     * @param array $result
     * @param array $listStudent
     * @param array $exam
     * @param array $department
     * @param array $listExamShiftDetail
     * @param array $listExamBank
     * @param string $examShiftName
     * @param array $listCandidateNumber
     * @return void
     */
    public function calculateByExam(array &$result, array $listStudent, array $exam, array $department, array $listExamShiftDetail, array $listExamBank, string $examShiftName, array &$listCandidateNumber)
    {
        $listExam = []; //danh sách thông tin các bài thi
        $listExamDetail = [];  //danh sách chi tiết về bài thi
        $ret = [];
        foreach ($listStudent as $student) {
            if (!array_key_exists($student['candidateNumber'], $listCandidateNumber)) {
                $ret[$student['candidateNumber']]['info'] = [
                    'student_name' => $student['studentName'],
                    'student_code' => $student['studentID'],
                    'exam_shift_detail_id' => $listExamShiftDetail[$exam['id']]['id'],
                    'candidate_number' => $student['candidateNumber'],
                    'department_name' => $department['department_name'],
                    'total' => 0,
                    'note' => '',
                ];
                $criterias = $listExamBank[$student['examBankId']]['criterias'];
                $phpWord = PHPIOFactory::load($student['path'] . '/' . $student['studentAssignment'][0]);
                // Lấy danh sách các sections trong tài liệu
                $sections = $phpWord->getSections();
                [$images, $footnotes, $footers] = $this->getListData($sections);
                foreach ($criterias as $index => $criteria) {
                    $itemCriteria = [
                        'point' => $criteria['point'],
                        'real_point' => $criteria['point'],
                        'flag' => true,
                        'property_name' => $criteria['property_name'],
                        'candidate_number' => $student['candidateNumber'],
                        'exam_shift_detail_id' => $listExamShiftDetail[$exam['id']]['id'],
                        'criteria_id' => "{$student['studentID']}{$index}",
                        'parent_criteria_id' => $criteria['id'],
                        'has_child' => false,
                        'parent_criteria_name' => $criteria['property_name'],
                        'exam_shift_name' => $examShiftName,
                        'department_name' => $department['department_name'],
                        'exam_bank_name' => $student['examBankName'],
                        'student_code' => $student['studentID'],
                    ];
                    $ret[$student['candidateNumber']]['criterias'][$criteria['id']] = $itemCriteria;
                    switch ($criteria['property_type']) {
                        case PropertyType::PAGE_SIZE_ALL:
                            foreach ($sections as $section) {
                                // page size trong bài thi
                                $pageSize = $section->getStyle()->getPaperSize();
                                $test[] = $pageSize;
                                // page size trong DB
                                if ($pageSize != $criteria['content']) {
                                    $this->setPointFail($ret, $student, $criteria);
                                }
                            }
                            break;
                        case PropertyType::HEADER_ALL:
                            $value = $criteria['content'];
                            $headerName = '';
                            foreach ($sections as $section) {
                                $headers = $section->getHeaders();
                                if (count($headers) > 0) {
                                    foreach ($headers as $header) {
                                        $elements = $header->getElements();
                                        foreach ($elements as $element) {
                                            if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                                $headerName .= $element->getText();
                                            }
                                        }
                                    }
                                }
                            }
                            if (!str_contains($this->stripUnicode($value), $this->stripUnicode($headerName))) {
                                $this->setPointFail($ret, $student, $criteria);
                            }
                            break;
                        case PropertyType::FOOTER_TYPE_ALL:
                            $this->checkFooterTypeAll($criteria, $footers, $student, $ret);
                            break;
                        case PropertyType::FOOTER_ALL:
                            $this->checkFooterAll($criteria, $footers, $student, $ret);
                            break;
                        case PropertyType::FOOTNOTE:
                            if (empty($footnotes)) {
                                $this->setPointFail($ret, $student, $criteria);
                            } else {
                                $this->setPointFail($ret, $student, $criteria);
                                $this->checkFootNote($criteria, $footnotes, $student, $ret, $itemCriteria);
                            }
                            break;
                        case PropertyType::APPLY_STYLE:
                            $this->checkApplyStyle($criteria, $student, $ret);
                            break;
                        case PropertyType::APPLY_STYLE_ALL:
                            $this->checkApplyStyleAll($criteria, $student, $ret);
                            break;
                        case PropertyType::MARGIN_LEFT_ALL:
                        case PropertyType::MARGIN_RIGHT_ALL:
                        case PropertyType::MARGIN_TOP_ALL:
                        case PropertyType::MARGIN_BOTTOM_ALL:
                            $this->checkMarginAll($sections, $criteria, $student, $ret);
                            break;
                        case PropertyType::IMAGE:
                            if (!$images) {
                                $this->setPointFail($ret, $student, $criteria);
                            } else {
                                $this->checkImages($student, $criteria, $ret, $images, $itemCriteria);
                            }
                            break;
                        case PropertyType::MODIFY_STYLE:
                            $this->checkModifyStyle($criteria, $student, $ret, $itemCriteria);
                            break;
                        case PropertyType::TITLE:
                            $title = $phpWord->getDocInfo()->getTitle();
                            $this->checkInfos($criteria, $student, $ret, $title);
                            break;
                        case PropertyType::AUTHOR:
                            $author = $phpWord->getDocInfo()->getCreator();
                            $this->checkInfos($criteria, $student, $ret, $author);
                            break;
                        default:
                            break;
                    }
                    $ret[$student['candidateNumber']]['info']['total'] += $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'];
                }
                $ret[$student['candidateNumber']]['info']['total'] = round($ret[$student['candidateNumber']]['info']['total'], 3);
                $listExam[$student['candidateNumber']] = $ret[$student['candidateNumber']]['info'];
                $listExamDetail[] = $ret[$student['candidateNumber']]['criterias'];
                $ret[$student['candidateNumber']]['info']['exam_shift_detail_id'] = $listExamShiftDetail[$exam['id']]['id'];
                $ret[$student['candidateNumber']]['info']['exam_bank_name'] = $student['examBankName'];
                $listCandidateNumber[$student['candidateNumber']] = $student['candidateNumber'];
                $result[] = $ret[$student['candidateNumber']]['info'];
            }
        }
        try {
            DB::begintransaction();
            ExamResult::insert($listExam); // remember: $data is array of associative array. Not just a single assoc array.
            foreach ($listExamDetail as $item) {
                ExamResultDetail::insert($item);
            }
            DB::commit();
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    /**
     * @param $sections
     * @return array
     */
    public function getListData($sections): array
    {
        $images = [];
        $footNotes = [];
        $footers = [];
        // Duyệt qua từng section để tìm hình ảnh
        foreach ($sections as $section) {
            $footerName = null;
            $footerType = null;
            if (count($section->getFooters()) > 0) {
                foreach ($section->getFooters() as $footer) {
                    $elements = $footer->getElements();
                    foreach ($elements as $element) {
                        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                            $footerName .= $element->getText();
                        }
                    }
                    $footerType = $footer->getType() == FooterType::DEFAULT ? FooterType::BLANK : $footer->getType();
                }

                if (!empty($footerName)) {
                    $footers[] = [
                        'footerName' => $footerName,
                        'footerType' => $footerType,
                    ];
                }
            }


            foreach ($section->getElements() as $element) {
                // Kiểm tra xem phần tử có phải là TextRun không
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    // Duyệt qua các phần tử con của TextRun để tìm hình ảnh
                    foreach ($element->getElements() as $index => $subElement) {
                        // Kiểm tra xem phần tử con có phải là hình ảnh không
                        if ($subElement instanceof \PhpOffice\PhpWord\Element\Image) {
                            $imageName = pathinfo($subElement->getName(), PATHINFO_FILENAME);

                            $images[$imageName] = [
                                'imageSource' => $subElement->getSource(),
                                'imageStyle' => $subElement->getStyle(),
                                'width' => round($subElement->getStyle()->getWidth() / 219, 1),
                                'height' => round($subElement->getStyle()->getHeight() / 269, 1),
                                'imageName' => $imageName,
                            ];
                        }
                        if ($subElement instanceof \PhpOffice\PhpWord\Element\Footnote) {
                            if (!empty($subElement->getElements()[1])) {
                                // Lấy nội dung của chú thích chân trang
                                $noteText = $subElement->getElements()[1]->getText(); // Assuming the footnote text is at index 1
                                // Lấy kiểu đánh số và vị trí của chú thích
                                $numberingType = $this->getFootnoteNumberingType($subElement);
                                $footnotePosition = $subElement->getPosition();
                                // Thêm thông tin về kiểu đánh số và vị trí vào mảng $footNotes
                                $footNotes[] = [
                                    'content' => $noteText,
                                    'numberingType' => $numberingType,
                                    'position' => $footnotePosition,
                                ];
                            }
                        }
                    }
                }
            }
        }
        return [$images, $footNotes, $footers];
    }

    /**
     * @param array $criteria
     * @param array $footers
     * @param array $student
     * @param array $ret
     * @return void
     */
    public function checkFooterTypeAll(array $criteria, array $footers, array $student, array &$ret)
    {
        foreach ($footers as $footer) {
            if ($footer['footerType'] != $criteria['content']) {
                $this->setPointFail($ret, $student, $criteria);
                break;
            }
        }
    }

    /**
     * @param array $criteria
     * @param array $footers
     * @param array $student
     * @param array $ret
     * @return void
     */
    public function checkFooterAll(array $criteria, array $footers, array $student, array &$ret)
    {
        foreach ($footers as $footer) {
            try {
                $content = $criteria['content'];
                if (!str_contains($this->stripUnicode($footer['footerName']), $this->stripUnicode($content))) {
                    $this->setPointFail($ret, $student, $criteria);
                    break;
                }
            } catch (\Throwable $th) {
                dd($criteria['content']);
            }
        }
    }

    /**
     * @param array $criteria
     * @param array $student
     * @param array $ret
     * @return void
     */
    public function checkApplyStyle(array $criteria, array $student, array &$ret)
    {
        if (!empty($student['style']) && !empty($criteria['content'])) {
            $content = json_decode($criteria['content'], true);
            $paragraphs = explode($this->_SEPARATOR, $content['value']);
            $this->paragraphWithout = array_merge($this->paragraphWithout, $paragraphs);
            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = $criteria['point'];
            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = true;
            // duyệt từng đoạn văn bản cần áp dụng style
            foreach ($paragraphs as $paragraph) {
                foreach ($student['style'] as $style) {
                    // kiểm tra trong file word có chứa đoạn văn ko, nếu có thì kiểm tra tên có trùng khớp
                    if (str_contains($this->stripUnicode($paragraph), $this->stripUnicode($style['text'])) && $content['key'] != $style['style']['name']) {
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = false;
                        break;
                    }
                }
            }
        } else $this->setPointFail($ret, $student, $criteria);
    }

    /**
     * @param array $criteria
     * @param array $student
     * @param array $ret
     * @return void
     */
    public function checkApplyStyleAll(array $criteria, array $student, array &$ret)
    {
        if ($student['style']) {
            $paragraphWithout = implode(',', $this->paragraphWithout);
            foreach ($student['style'] as $style) {
                if (!str_contains($this->stripUnicode($style['text']), $this->stripUnicode($paragraphWithout)) && $criteria['content'] != $style['style']['name']) {
                    break;
                }
            }
        } else $this->setPointFail($ret, $student, $criteria);
    }

    /**
     * @param array $criteria
     * @param array $footnotes
     * @param array $student
     * @param array $ret
     * @param array $itemCriteria
     * @return void
     */
    public function checkFootNote(array $criteria, array $footnotes, array $student, array &$ret, array $itemCriteria)
    {
        try {
            $content = json_decode($criteria['content'], true);
            foreach ($footnotes as $footnote) {
                foreach ($content['value'] as $item) {
                    $this->setCriteriaChildren($itemCriteria, $student, $ret, $item, $criteria);
                    if (str_contains($this->stripUnicode($content['key']), $this->stripUnicode($footnote['content']))) {
                        if ((int)$item['key'] == PropertyType::FOOTNOTE_TYPE) {
                            if ($footnote['numberingType'] == $item['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $item['point'];
                            } else $this->setCriteriaRealPointChildFail($item, $criteria, $student, $ret);
                        } elseif ((int)$item['key'] == PropertyType::INSERT_FOOTNOTE || (int)$item['key'] == PropertyType::FOOTNOTE_CONTENT) {
                            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $item['point'];
                        }
                    }
                }
                break;
            }
        } catch (\Throwable $th) {
            \Log::info('ApiWord.checkFootNote', [$th]);
        }
    }

    /**
     * @param $footnote
     * @return string|null
     */
    private function getFootnoteNumberingType($footnote): ?string
    {
        // Lấy danh sách các phần tử trong chú thích
        $footnoteElements = $footnote->getElements();

        // Kiểm tra xem chú thích có phần tử không
        if (!empty($footnoteElements)) {
            // Lấy phần tử đầu tiên của chú thích
            $firstElement = $footnoteElements[0];

            // Lấy nội dung của phần tử đầu tiên
            $firstElementText = $firstElement->getText();

            // Kiểm tra ký tự đầu tiên của nội dung để xác định kiểu đánh số
            $firstCharacter = mb_substr($firstElementText, 0, 1);

            // Thiết lập kiểu đánh số tương ứng
            $numberingType = '';
            switch ($firstCharacter) {
                case '*':
                    $numberingType = NumberingType::NUMBER_MATH;
                    break;
                case 'a':
                    $numberingType = NumberingType::NUMBER_ABC;
                    break;
                case 'i':
                    $numberingType = NumberingType::LOWER_CASE_ROMAN_NUMBER;
                    break;
                default:
                    $numberingType = NumberingType::NUMBER_123;
                    break;
            }

            return $numberingType;
        }

        return null;
    }


    /**
     * @param array $student
     * @param array $criteria
     * @param array $ret
     * @param array $images
     * @param array $itemCriteria
     * @return void
     */
    public function checkImages(array $student, array $criteria, array &$ret, array $images, array $itemCriteria)
    {
        try {
            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = 0;
            $content = json_decode($criteria['content'], true);
            $key = pathinfo($content['key'], PATHINFO_FILENAME);
            // mặc định cho điểm tên
            foreach ($content['value'] as $item) {
                $this->setCriteriaChildren($itemCriteria, $student, $ret, $item, $criteria);
                if (!empty($images)) {
                    switch ((int)$item['key']) {
                        case PropertyType::NAME_IMAGE:
                            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $item['point'];
                            break;
                        case PropertyType::WIDTH_IMAGE:
                            foreach ($images as $image) {
                                if ($image['width'] == $item['value']) {
                                    $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $item['point'];
                                    break;
                                } else $this->setCriteriaRealPointChildFail($item, $criteria, $student, $ret);
                            }
                            break;
                        case PropertyType::HIGH_IMAGE:
                            $inValid = true;
                            foreach ($images as $image) {
                                if ($image['height'] == $item['value']) {
                                    $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $item['point'];
                                    $inValid = false;
                                    break;
                                }
                            }
                            if ($inValid) $this->setCriteriaRealPointChildFail($item, $criteria, $student, $ret);
                            break;
                    }
                }
            }
//                if (array_key_exists($key, $images)) {
//                    foreach ($content['value'] as $item) {
//                        switch ((int)$item['key']) {
//                            case PropertyType::NAME_IMAGE:
//                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $item['point'];
//                                break;
//                            case PropertyType::WIDTH_IMAGE:
//                                if ($images[$key]['width'] == $item['value']) {
//                                    $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $item['point'];
//                                }
//                                break;
//                            case PropertyType::HIGH_IMAGE:
//                                if ($images[$key]['height'] == $item['value']) {
//                                    $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $item['point'];
//                                }
//                                break;
//                        }
//                    }
//                }
        } catch (\Throwable $th) {
            \Log::info('ApiWord.checkImages', [$th]);
        }
    }

    /**
     * @param array $ret
     * @param array $student
     * @param $criteria
     * @return void
     */
    public
    function setPointFail(array &$ret, array $student, $criteria)
    {
        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = false;
        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = 0;
    }

    /**
     * @param $sections
     * @param $criteria
     * @param $student
     * @param $ret
     * @return void
     */
    public
    function checkMarginAll($sections, $criteria, $student, &$ret)
    {
        $value = 0;
        foreach ($sections as $section) {
            switch ((int)$criteria['property_type']) {
                case PropertyType::MARGIN_LEFT_ALL:
                    $value = round($section->getStyle()->getMarginLeft() / 569, 1);
                    break;
                case PropertyType::MARGIN_RIGHT_ALL:
                    $value = round($section->getStyle()->getMarginRight() / 569, 1);
                    break;
                case PropertyType::MARGIN_TOP_ALL:
                    $value = round($section->getStyle()->getMarginTop() / 569, 1);
                    break;
                case PropertyType::MARGIN_BOTTOM_ALL:
                    $value = round($section->getStyle()->getMarginBottom() / 569, 1);
                    break;
            }
            if ((double)$value != (double)$criteria['content']) {
                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = false;
                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = 0;
            }
        }
    }

    /**
     * @param array $itemCriteria
     * @param array $student
     * @param array $ret
     * @param array $value
     * @return void
     */
    public function setCriteriaChildren(array $itemCriteria, array $student, array &$ret, array $value, array $criteria)
    {
        try {
            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['has_child'] = true;
            $itemCriteria['parent_criteria_id'] = $itemCriteria['criteria_id'];
            $itemCriteria['point'] = $value['point'];
            $itemCriteria['real_point'] = $value['point'];
            $itemCriteria['property_name'] = PropertyType::getDescription((int)$value['key']);
            $itemCriteria['criteria_id'] = $value['key'];
            $ret[$student['candidateNumber']]['criterias']["{$criteria['id']}{$itemCriteria['criteria_id']}"] = $itemCriteria;
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    /**
     * @param array $value
     * @param array $criteria
     * @param array $student
     * @param array $ret
     * @return void
     */
    public function setCriteriaRealPointChildFail(array $value, array $criteria, array $student, array &$ret)
    {
        $ret[$student['candidateNumber']]['criterias']["{$criteria['id']}{$value['key']}"]['real_point'] = 0;
    }

    /**
     * @param array $criteria
     * @param array $student
     * @param array $ret
     * @param array $itemCriteria
     * @return void
     */
    public function checkModifyStyle(array $criteria, array $student, array &$ret, array $itemCriteria)
    {
        try {
            $content = json_decode($criteria['content'], true);
            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = 0;
            $key = $content['key'];
            $values = $content['value'] ?? [];
            $style = [];
            if (!empty($student['style'])) {
                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = false;
                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = 0;
                foreach ($student['style'] as $item) {
                    // Kiểm tra nếu style có font là Times New Roman
                    if ($item['style']['name'] == $key) {
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = true;
                        $style = $item['style'];
                        break;
                    }
                }
            }
            foreach ($values as $value) {
                $this->setCriteriaChildren($itemCriteria, $student, $ret, $value, $criteria);
                if (!empty($style)) {
                    switch ($value['key']) {
                        case PropertyType::FONT:
                            $style['font'] = !empty($style['font']) ? $style['font'] : FontType::TIME_NEW_ROMAN;
                            if ($style['font'] == $value['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        case PropertyType::FONT_SIZE:
                            if ($style['font_size'] == $value['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        case PropertyType::FONT_STYLE:
                            if ($style['font_style'] == $value['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        case PropertyType::FONT_COLOR:
                            if ($style['font_color'] == $value['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        case PropertyType::SPACING_BEFORE:
                            if ($style['space_before'] == $value['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        case PropertyType::SPACING_AFTER:
                            if ($style['space_after'] == $value['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        case PropertyType::SPECIAL_HANGING:
                            if ($style['indent_hanging'] == $value['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        case PropertyType::INDENTATION_LEFT:
                            if ($style['indent_left'] == $value['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        case PropertyType::ALIGNMENT_CENTERD:
                        case PropertyType::ALIGNMENT_RIGHT:
                        case PropertyType::NUMBERING:
                        case PropertyType::ALIGNMENT_JUSTIFITED:
                        case PropertyType::ALL_CAPS:
                            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            break;
                        case PropertyType::ALIGNMENT_LEFT:
                            if (!$style['alignment']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        case PropertyType::LINE_SPACING:
                            if ($style['line_spacing_value'] == $value['value']) {
                                $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] += $value['point'];
                            } else $this->setCriteriaRealPointChildFail($value, $criteria, $student, $ret);
                            break;
                        default:
                            break;
                    }
                }
            }
        } catch (\Throwable $th) {
            \Log::info('ApiWord.checkModifyStyle', [$th]);
        }
    }

    /**
     * @param $criteria
     * @param $student
     * @param $ret
     * @param $value
     * @return void
     */
    public
    function checkInfos($criteria, $student, &$ret, $value)
    {
        $content = json_decode($criteria['content'], true);
        if (is_array($content) && !array_key_exists($content['key'], $content) && !array_key_exists($content['value'], $content)) {
            switch ((int)$content['key']) {
                case InfoType::STUDENT_NAME:
                    if (!str_contains($this->stripUnicode($value), $this->stripUnicode($student['studentName']))) {
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = false;
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = false;
                    }
                    break;
                case InfoType::STUDENT_CODE:
                    if (!str_contains($this->stripUnicode($value), $this->stripUnicode($student['studentID']))) {
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = false;
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = false;
                    }
                    break;
                case InfoType::STUDENT_CODE_NAME:
                    $this->stripUnicode($student['studentName']);

                    if (!str_contains($this->stripUnicode($value), $this->stripUnicode($student['studentName'])) || !str_contains($this->stripUnicode($value), $this->stripUnicode($student['studentID']))) {
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = false;
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = false;
                    }
                    break;
                case InfoType::OTHER:
                    if (!str_contains($this->stripUnicode($value), $this->stripUnicode($content['value']))) {
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = false;
                        $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = 0;
                    }
                    break;
            }
        } else {
            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['flag'] = false;
            $ret[$student['candidateNumber']]['criterias'][$criteria['id']]['real_point'] = 0;
        }
    }

    /**
     * Xóa dấu câu trong chuỗi
     * @param string $str Chuỗi cần xóa dấu
     * @return string $str
     * **/
    function stripUnicode(string &$str): string
    {
        $str = mb_strtolower($str);
        $unicode = array(
            'a' => 'a|á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'A' => 'A|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd' => 'd|đ',
            'D' => 'D|Đ',
            'e' => 'e|é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'E' => 'E|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i' => 'i|í|ì|ỉ|ĩ|ị',
            'I' => 'I|Í|Ì|Ỉ|Ĩ|Ị',
            'o' => 'o|ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'O' => 'O|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u' => 'u|ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'U' => 'U|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y' => 'y|ý|ỳ|ỷ|ỹ|ỵ',
            'Y' => 'Y|Ý|Ỳ|Ỷ|Ỹ|Ỵ'
        );
        foreach ($unicode as $nonUnicode => $uni) $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        return str_replace(' ', '', $str);
    }

    private function getTextContent($paragraph, $ns_w)
    {
        $text = '';

        // Iterate through text elements in the paragraph
        foreach ($paragraph->xpath('.//w:t') as $textElement) {
            // Append text content to the result
            $text .= (string)$textElement;
        }


        return $text;
    }

    /**
     * @param string $filePath
     * @return array
     */
    public
    function getListStyles(string $filePath): array
    {
        try {
            $ret = [];
            // Đọc file DOCX như một tệp nén
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                // Đọc nội dung của file styles.xml trong tệp nén
                $stylesContent = $zip->getFromName('word/styles.xml');
                // Đọc nội dung của file word/document.xml trong tệp nén
                $documentContent = $zip->getFromName('word/document.xml');
                $zip->close();
                if (!empty($stylesContent) && !empty($documentContent)) {
                    // Lấy danh sách các style từ file styles.xml
                    $styles = $this->extractStyles($stylesContent);
                    // Lấy danh sách các đoạn văn cùng với thông tin style tương ứng
                    $ret = $this->extractContentWithStyles($documentContent, $styles);
                } else {
                    echo "Failed to read styles.xml or document.xml";
                }
            } else {
                echo "Failed to open the DOCX file";
            }
            return $ret;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param $stylesXml
     * @return array
     */
    private function extractStyles($stylesXml): array
    {
        $styles = [];
        // Load XML content
        $xml = simplexml_load_string($stylesXml);
        $namespaces = $xml->getDocNamespaces();
        $ns_w = $xml->getNamespaces(true)['w'];
        // Iterate through styles
        foreach ($xml->xpath('//w:style') as $style) {
            $styleName = (string)($style->attributes($ns_w)->styleId);
            // Extract font name
            $fontName = null;
            $fontElement = $style->xpath('.//w:rFonts');
            if (!empty($fontElement)) {
                $fontName = (string)($fontElement[0]->attributes($ns_w)->ascii);
            }

            // Extract font size
            $fontSize = null;
            $fontSizeElement = $style->xpath('.//w:sz');
            if (!empty($fontSizeElement)) {
                $fontSize = (string)($fontSizeElement[0]->attributes($ns_w)->val);
            }

            // Extract font style
            $fontStyle = null;
            $fontStyleElement = $style->xpath('.//w:b');
            if (!empty($fontStyleElement)) {
                $fontStyle = 'Bold';
            } else {
                $fontStyleElement = $style->xpath('.//w:i');
                if (!empty($fontStyleElement)) {
                    $fontStyle = 'Italic';
                }
            }

            // Extract font color
            $fontColor = null;
            $fontColorElement = $style->xpath('.//w:color');
            if (!empty($fontColorElement)) {
                $fontColor = (string)($fontColorElement[0]->attributes($ns_w)->val);
            }

            // Extract indent information
            $indentLeft = null;
            $indentRight = null;
            $indentHanging = null;
            $indentFirstLine = null;
            $indentElement = $style->xpath('.//w:ind');
            if (!empty($indentElement)) {
                $indentLeft = (string)($indentElement[0]->attributes($ns_w)->left / 1440); // Convert from twips to inches
                $indentRight = (string)($indentElement[0]->attributes($ns_w)->right / 1440); // Convert from twips to inches
                $indentHanging = (string)($indentElement[0]->attributes($ns_w)->hanging / 1440); // Convert from twips to inches
                $indentFirstLine = (string)($indentElement[0]->attributes($ns_w)->firstLine / 1440); // Convert from twips to inches
            }

            // Extract spacing information
            $spaceBefore = null;
            $spaceAfter = null;
            $spacingElement = $style->xpath('.//w:spacing');
            if (!empty($spacingElement)) {
                $spaceBefore = (string)($spacingElement[0]->attributes($ns_w)->before / 20); // Convert from twips to points
                $spaceAfter = (string)($spacingElement[0]->attributes($ns_w)->after / 20); // Convert from twips to points
            }

            // Extract line spacing
            $lineSpacingType = null;
            $lineSpacingValue = null;
            $lineSpacingElement = $style->xpath('.//w:spacing');
            if (!empty($lineSpacingElement)) {
                $lineSpacingType = (string)($lineSpacingElement[0]->attributes($ns_w)->lineRule ?? null);
                switch ($lineSpacingType) {
                    case 'auto':
                        $lineSpacingType = 'Automatic';
                        break;
                    case 'atLeast':
                        $lineSpacingType = 'At Least';
                        break;
                    case 'exact':
                        $lineSpacingType = 'Exactly';
                        break;
                    case 'multiple':
                        $lineSpacingType = 'Multiple';
                        break;
                    default:
                        $lineSpacingType = null;
                }

                if ($lineSpacingType === 'Multiple' || $lineSpacingType === 'Automatic') {
                    $lineSpacingValue = (string)($lineSpacingElement[0]->attributes($ns_w)->line / 240.0); // Convert from twips to lines
                } else if ($lineSpacingType === 'At Least' || $lineSpacingType === 'Exactly') {
                    $lineSpacingValue = (string)($lineSpacingElement[0]->attributes($ns_w)->line / 20); // Convert from twips to points
                }
            }

            // Extract alignment
            $alignment = null;
            $alignmentElement = $style->xpath('.//w:jc');
            if (!empty($alignmentElement)) {
                $alignmentValue = (string)($alignmentElement[0]->attributes($ns_w)->val);
                switch ($alignmentValue) {
                    case 'left':
                        $alignment = 'Left';
                        break;
                    case 'right':
                        $alignment = 'Right';
                        break;
                    case 'center':
                        $alignment = 'Center';
                        break;
                    case 'both':
                        $alignment = 'Both';
                        break;
                    case 'distribute':
                        $alignment = 'Distributed';
                        break;
                    case 'justify':
                        $alignment = 'Justified';
                        break;
                    default:
                        $alignment = null;
                }
            }

            $styles[] = [
                'name' => $styleName,
                'font' => $fontName,
                'font_size' => $fontSize / 2,
                'font_style' => $fontStyle,
                'font_color' => $fontColor,
                'indent_left' => round($indentLeft, 2),
                'indent_right' => round($indentRight, 2),
                'indent_hanging' => round($indentHanging, 2),
                'indent_first_line' => round($indentFirstLine, 2),
                'space_before' => $spaceBefore,
                'space_after' => $spaceAfter,
                'line_spacing_type' => $lineSpacingType,
                'line_spacing_value' => $lineSpacingValue,
                'alignment' => $alignment,
            ];
        }


        return $styles;
    }

    /**
     * @param $documentXml
     * @param $styles
     * @return array
     */
    private
    function extractContentWithStyles($documentXml, $styles): array
    {
        $contentWithStyles = [];

        // Load XML content
        $xml = simplexml_load_string($documentXml);

        // Register namespaces
        $namespaces = $xml->getDocNamespaces();
        $ns_w = $xml->getNamespaces(true)['w'];

        // Iterate through paragraphs
        foreach ($xml->xpath('//w:p') as $paragraph) {
            // Get paragraph style ID
            $styleId = (string)($paragraph->xpath('./w:pPr/w:pStyle/@w:val')[0] ?? null);
            $textContent = $this->getTextContent($paragraph, $ns_w);

            // Find the corresponding style
            $style = null;
            foreach ($styles as $s) {
                if ($s['name'] === $styleId) {
                    $style = $s;
                    break;
                }
            }

            // If no style found, use default style
            if ($style === null) {
                // Find the style with the name 'Normal' and use its properties
                foreach ($styles as $s) {
                    if ($s['name'] === 'Normal') {
                        $style = $s;
                        break;
                    }
                }
            }
            // bỏ qua text xuống dòng
            if (!empty($textContent)) {
                // Add content with style to the list
                $contentWithStyles[] = [
                    'type' => StyleType::PARAGRAPH,
                    'style' => $style,
                    'text' => $textContent,
                ];
            }
        }

        // Iterate through textboxes
        foreach ($xml->xpath('//w:pict/v:shape') as $shape) {
            if ($shape->xpath('./v:textbox')) {
                $textboxContent = $shape->xpath('./v:textbox/w:txbxContent/w:p/w:r/w:t')[0] ?? null;
                if ($textboxContent !== null) {
                    $contentWithStyles[] = [
                        'type' => StyleType::TEXT_BOX,
                        'content' => (string)$textboxContent,
                    ];
                }
            }
        }
        return $contentWithStyles;
    }
}
