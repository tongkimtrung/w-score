<?php

namespace App\Http\Controllers;

use App\Enums\StyleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ExamBank;
use App\Models\Criteria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use PhpOffice\PhpWord\Style\Font;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use PhpOffice\PhpWord\IOFactory as PHPIOFactory;
use ZipArchive;
use App\Enums\PropertyType;
use App\Enums\InfoType;
use App\Enums\PageSize;
use App\Enums\Style;
use App\Enums\FontStyleType;
use App\Enums\FontColor;
use App\Enums\FontType;
use App\Enums\NumberingType;
use App\Enums\ApplyStyle;
use App\Enums\SelectionType;
use App\Enums\FooterType;

class ApiExamBankController extends Controller
{
    private $_PATH_FILE = 'app/exams';
    private $_SEPARATOR = 'ーー';

    public function __construct()
    {
        $this->_PATH_FILE = storage_path($this->_PATH_FILE);
    }

    public function get()
    {
        return $this->sendResponseSuccess(ExamBank::with('departments', 'criterias')->get()->toArray());
    }

    /**
     * @param Request $request
     */
    public function save(Request $request)
    {
        try {
            $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
            if (!$receiver->isUploaded()) {
                // file not uploaded
                return $this->sendResponseError();
            }
            $fileReceived = $receiver->receive(); // receive file
            if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
                $validator = Validator::make(
                    json_decode($request->param, true),
                    [
                        'exam_bank_code' => 'required|unique:exam_banks,exam_bank_code',
                        'exam_bank_name' => 'required',
                        'file_size' => '',
                        'file_name' => '',
                    ],
                    [
                        'exam_bank_code.required' => 'Mã đề thi không được để trống',
                        'exam_bank_code.unique' => 'Mã đề thi đã tồn tại',
                        'exam_bank_name.required' => 'Tên đề thi không được để trống',
                    ],
                );


                if ($validator->fails()) {
                    return $this->sendResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ['errors' => $validator->errors(), 'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY]);
                }
                $attributes = $validator->validated();
                $file = $fileReceived->getFile();
                $ext = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
                if ($ext !== 'docx') {
                    return $this->sendResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ['errors' => ['fileData' => 'file không đúng định dạng'], 'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY]);
                } else {
                    try {
                        if (!File::exists($this->_PATH_FILE)) {
                            File::makeDirectory($this->_PATH_FILE, 0777, true);
                        }
                        $path = $this->_PATH_FILE . "/{$attributes['exam_bank_code']}";
                        $file->move($path, $file->getClientOriginalName());
                        $ret = [];
                        // Đọc file DOCX như một tệp nén
                        $zip = new ZipArchive();
                        if ($zip->open($path . '/' . $file->getClientOriginalName()) === true) {
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
                                return $this->sendResponseError(['message' => 'file trống']);
                            }
                        } else {
                            return $this->sendResponseError(['message' => 'Lỗi đọc file']);
                        }
                        $attributes['exam_bank_content'] = implode($this->_SEPARATOR, array_column($ret, 'text'));
                        ExamBank::insert($attributes);
                    } catch (\Exception $e) {
                        return $this->sendResponseError(['errors' => ['message' => $e->getMessage()], 'errorCode' => Response::HTTP_UNPROCESSABLE_ENTITY]);
                    }
                }
                return $this->sendResponseSuccess();
            }
        } catch (\Throwable $th) {
            return $this->sendResponseError();
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
    private function extractContentWithStyles($documentXml, $styles): array
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

    /**
     * @param $paragraph
     * @param $ns_w
     * @return string
     */
    private function getTextContent($paragraph, $ns_w): string
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
     * @param Request $request
     * @param $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        $attribute = $request->validate([
            'exam_bank_name' => 'required',
            'exam_bank_code' => "required|unique:exam_banks,exam_bank_code,{$request->exam_bank_code},exam_bank_code",
        ],
            [
                'exam_bank_name.required' => 'Tên đề thi không được để trống',
                'exam_bank_code.required' => 'Mã đề thi không được để trống',
                'exam_bank_code.unique' => 'Mã đề thi đã tồn tại',
            ]);
        ExamBank::find($request->id)->update($attribute);
    }

    public function getSettings()
    {
        return $this->sendResponseSuccess(ExamBank::has('criterias')->get()->toArray());
    }

    /**
     * Lấy danh sách các thuộc tính
     */
    public function configureExam($id)
    {
        try {
            $examBank = ExamBank::withSum('criterias', 'point')->find($id);
            if (empty($examBank)) {
                return $this->sendResponseError();
            }
            // làm tròn điểm
            $examBank->criterias_sum_point = round($examBank->criterias_sum_point, 3);
            $paragraphs = explode($this->_SEPARATOR, $examBank->exam_bank_content);
            $result = [
                'data' => $examBank->toArray(),
                'separator' => $this->_SEPARATOR,
                'paragraphs' => $paragraphs,
                'typeProperty' => collect(PropertyType::getInstances())->map(function ($instance) {
                    return $instance->value;
                })->toArray(),
                'infoTypeEnums' => collect(InfoType::getInstances())->map(function ($instance) {
                    return [
                        'value' => $instance->value,
                        'description' => $instance->description,
                    ];
                })->toArray(),
                'pageSize' => collect(PageSize::getInstances())->values()->map(function ($instance) {
                    return [
                        'value' => $instance->description,
                        'description' => $instance->description,
                    ];
                })->toArray(),
                'style' => collect(Style::getInstances())->values()->map(function ($instance) {
                    return [
                        'value' => $instance->value,
                        'description' => $instance->description,
                    ];
                })->toArray(),
                'styleEnum' => collect(Style::getInstances())->map(function ($instance) {
                    return [
                        'value' => $instance->value,
                        'description' => $instance->description,
                    ];
                })->toArray(),
                'applyStyle' => collect(ApplyStyle::getInstances())->values()->map(function ($instance) {
                    return [
                        'value' => $instance->description,
                        'description' => $instance->value,
                    ];
                })->toArray(),
                'applyStyleEnum' => collect(ApplyStyle::getInstances())->map(function ($instance) {
                    return [
                        'value' => $instance->description,
                        'description' => $instance->value,
                    ];
                })->toArray(),
                'fontColor' => collect(FontColor::getInstances())->values()->map(function ($instance) {
                    return [
                        'value' => $instance->value,
                        'description' => $instance->description,
                    ];
                })->toArray(),
                'fontType' => collect(FontType::getInstances())->values()->map(function ($instance) {
                    return [
                        'value' => $instance->value,
                        'description' => $instance->value,
                    ];
                })->toArray(),
                'numberType' => collect(NumberingType::getInstances())->values()->map(function ($instance) {
                    return [
                        'value' => $instance->value,
                        'description' => $instance->value,
                    ];
                })->toArray(),
                'alignment' => collect([PropertyType::ALIGNMENT_LEFT, PropertyType::ALIGNMENT_CENTERD, PropertyType::ALIGNMENT_RIGHT, PropertyType::ALIGNMENT_JUSTIFITED])->map(function ($value) {
                    switch ($value) {
                        case PropertyType::ALIGNMENT_LEFT:
                            $description = 'Căn trái';
                            break;
                        case PropertyType::ALIGNMENT_RIGHT:
                            $description = 'Căn phải';
                            break;
                        case PropertyType::ALIGNMENT_CENTERD:
                            $description = 'Căn giữa';
                            break;
                        case PropertyType::ALIGNMENT_JUSTIFITED:
                            $description = 'Căn đều 2 bên';
                            break;
                        default:
                            $description = '';
                            break;
                    }
                    return [
                        'value' => $value,
                        'description' => $description
                    ];
                }),
                'footerType' => collect(FooterType::getInstances())->values()->map(function ($instance) {
                    return [
                        'value' => $instance->value,
                        'description' => $instance->description,
                    ];
                })->toArray(),
                'selection' => collect(SelectionType::getInstances())
                    ->reject(function ($instance) {
                        return in_array($instance->value, [SelectionType::FALSE, SelectionType::DEFAULT]);
                    })->values()
                    ->map(function ($instance) {
                        return [
                            'value' => $instance->description,
                            'description' => $instance->value,
                        ];
                    }),
                'fontStyling' => collect(FontStyleType::getInstances())->values()->map(function ($instance) {
                    return [
                        'value' => $instance->value,
                        'description' => $instance->description,
                    ];
                })->toArray(),
                'infoType' => collect(InfoType::getInstances())->values()->map(function ($instance) {
                    switch ($instance->value) {
                        case InfoType::STUDENT_NAME:
                            $description = 'Họ và tên';
                            break;
                        case InfoType::STUDENT_CODE:
                            $description = 'Mã sinh viên';
                            break;
                        case InfoType::STUDENT_CODE_NAME:
                            $description = 'Họ tên và mã sinh viên';
                            break;
                        case InfoType::OTHER:
                            $description = 'Khác';
                            break;
                        default:
                            $description = '';
                            break;
                    }
                    return [
                        'value' => $instance->value,
                        'description' => $description,
                    ];
                })->toArray(),
            ];
            return $this->sendResponseSuccess($result);

        } catch (\Throwable $th) {
            return $this->sendResponseError();
        }
    }

    /**
     * Lưu tiêu chí
     * @param Request $request
     * @return JsonResponse|object|void
     */
    public function saveCriteria(Request $request)
    {
        try {
            $attributes = $request->validate([
                '*.exam_bank_id' => 'required|numeric', // 'exam_bank_id' là bắt buộc và phải là số
                '*.content' => 'required', // 'content' là bắt buộc và phải là JSON
                '*.page' => '',
                '*.paragraph' => '',
                '*.property_type' => 'required|integer',
                '*.property_name' => 'required|string',
                '*.priority' => 'required|integer',
                '*.point' => 'required|numeric',
            ]);

            try {
                DB::beginTransaction();
                Criteria::insert($attributes);
                $examBankId = reset($attributes)['exam_bank_id'];
                $totalPoint = round(Criteria::where('exam_bank_id', $examBankId)->sum('point'), 3);
                DB::commit();
                return $this->sendResponseSuccess(['totalPoint' => $totalPoint]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return $this->sendResponseError();
            }
        } catch (\Throwable $th) {
            dd($th);
            return $this->sendResponseError();
        }
    }

    /**
     * Lấy danh sách tiêu chí
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getCriteria(Request $request)
    {
        $criterias = Criteria::where('exam_bank_id', $request->id)->get();
        if ($criterias) {
            return $this->sendResponseSuccess($criterias->toArray());
        } else {
            return $this->sendResponseError();
        }
    }

    public function delete($id)
    {
        ExamBank::where('id', $id)->delete();
    }
}
