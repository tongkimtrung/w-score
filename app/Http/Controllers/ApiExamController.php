<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamShift;
use App\Models\ExamShiftDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApiExamController extends Controller
{
    public function get()
    {
        return $this->sendResponseSuccess(Exam::all()->toArray());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getExamShifts(Request $request)
    {
        return $this->sendResponseSuccess(Exam::with('examShifts.departments', 'examShifts.examBanks', 'examShifts.examShiftDetails')->where('id', $request->id)->first()->toArray());
    }

    /**
     * @param Request $request
     * @return void
     */
    public function save(Request $request)
    {
        //validate dữ liệu
        $attribute = $request->validate([
            'exam_code' => 'required|unique:exams,exam_code',
            'exam_name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'note' => '',
            'listExamShift.*.exam_shift_code' => 'required|unique:exam_shifts,exam_shift_code',
            'listExamShift.*.exam_shift_name' => 'required',
            'listExamShift.*.start_date' => 'required',
            'listExamShift.*.end_date' => 'required',
            'listExamShift.*.departments' => 'required',
            'listExamShift.*.exam_bank_id' => 'required',
            'listExamShift.*.note' => '',
        ],
            [
                'exam_code.required' => 'Mã kì thi không được để trống',
                'exam_code.unique' => 'Mã kì thi đã tồn tại trong hệ thống',
                'exam_name.required' => 'Tên kì thi không được để trống',
                'start_date.required' => 'Ngày bắt đầu không được để trống',
                'end_date.required' => 'Ngày kết thúc không được để trống',
                'listExamShift.*.exam_shift_code.required' => 'Mã ca thi không được để trống',
                'listExamShift.*.exam_shift_name.unique' => 'Mã ca thi đã tồn tại trong hệ thống',
                'listExamShift.*.exam_shift_name.required' => 'Tên ca thi không được để trống',
                'listExamShift.*.start_date.required' => 'Ngày bắt đầu không được để trống',
                'listExamShift.*.end_date.required' => 'Ngày kết thúc không được để trống',
                'listExamShift.*.departments.required' => 'Phòng thi không được để trống',
                'listExamShift.*.exam_bank_id.required' => 'Đề thi không được để trống',
            ]);

        $attributeExam = [
            'exam_code' => $attribute['exam_code'],
            'exam_name' => $attribute['exam_name'],
            'start_date' => $this->convertDateTime($attribute['start_date']),
            'end_date' => $this->convertDateTime($attribute['end_date']),
            'note' => $attribute['note'],
        ];

        try {
            DB::beginTransaction();
            $examId = Exam::insertGetId($attributeExam);
            $listExamShiftDetail = [];
            $attributeExamShift = [];
            foreach ($attribute['listExamShift'] as $key => $examShift) {
                $attributeExamShift[] = [
                    'exam_shift_code' => $examShift['exam_shift_code'],
                    'exam_shift_name' => $examShift['exam_shift_name'],
                    'start_date' => $this->convertDateTime($examShift['start_date']),
                    'end_date' => $this->convertDateTime($examShift['end_date']),
                    'exam_id' => $examId,
                ];
                foreach ($examShift['departments'] as $departmantId) {
                    $objExamShiftDetail['department_id'] = $departmantId;
                    //chi tiết đề
                    foreach ($examShift['exam_bank_id'] as $examBankId) {
                        $objExamShiftDetail['exam_bank_id'] = $examBankId;
                        $listExamShiftDetail[$key][] = $objExamShiftDetail;
                    }
                }
            }
            //thêm mới ca thi
            ExamShift::insert($attributeExamShift);
            $startID = DB::select('select last_insert_id() as id');
            $startID = $startID[0]->id; // This will return 601
            $lastID = $startID + count($attribute['listExamShift']) - 1; // this will return 603
            $index = 0;
            $attributeExamShiftDetail = [];
            //thêm mã ca thi vào chi tiết ca thi
            for ($i = $startID; $i <= $lastID; $i++) {
                foreach ($listExamShiftDetail[$index] as $key => $examShiftDetail) {
                    $examShiftDetail['exam_shift_id'] = $i;
                    $attributeExamShiftDetail[] = $examShiftDetail;
                }
                $index++;
            }
            ExamShiftDetail::insert($attributeExamShiftDetail);
            DB::commit();
        } catch (\Throwable $th) {
            return $this->sendResponseError(['errors' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * @param Request $request
     * @return void
     */
    public function update(Request $request)
    {
        //validate dữ liệu
        $attribute = $request->validate([
            'exam_code' => "required|unique:exams,exam_code,{$request->id}",
            'exam_name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'note' => '',
        ],
            [
                'exam_code.required' => 'Mã kì thi không được để trống',
                'exam_code.unique' => 'Mã kì thi đã tồn tại',
                'exam_name.required' => 'Tên kì thi không được để trống',
                'start_date.required' => 'Ngày bắt đầu không được để trống',
                'end_date.required' => 'Ngày kết thúc không được để trống',
            ]);

        $attribute['start_date'] = $this->convertDateTime($attribute['start_date']);
        $attribute['end_date'] = $this->convertDateTime($attribute['end_date']);
        Exam::find($request->id)->update($attribute);
    }

    /**
     * Xóa kì thi
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        try {
            $examShift = DB::table('exam_shifts')->whereIn('exam_id', [$id]);
            $examShiftDetail = DB::table('exam_shift_details')->whereIn('exam_shift_id', $examShift->pluck('id')->toArray());
            $examResult = DB::table('exam_results')->whereIn('exam_shift_detail_id', $examShiftDetail->pluck('id')->toArray());
            $examResultDetail = DB::table('exam_result_details')->whereIn('exam_shift_detail_id', $examShiftDetail->pluck('id')->toArray());
            DB::beginTransaction();
            //xóa kết quả chi tiết của bài thi
            $examResultDetail->delete();
            //xóa kết quả chi của bài thi
            $examResult->delete();
            //xóa các ca thi chi tiết trong kì thi
            $examShiftDetail->delete();
            //xóa ca thi
            $examShift->delete();
            //xóa kì thi
            Exam::find($id)->delete();
            //xóa thư mục lưu kết quả của kì thi
            DB::commit();
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
        }
    }
}
