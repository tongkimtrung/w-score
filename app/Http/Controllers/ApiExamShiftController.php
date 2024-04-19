<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enums\FormMode;
use App\Models\ExamShift;
use App\Models\ExamShiftDetail;
use Illuminate\Support\Facades\DB;

class ApiExamShiftController extends Controller
{
    public function getWithDepartment()
    {

    }

    public function checkExamShiftCodeExits(Request $request)
    {
        //insert
        if ($request->mode == 1) {
            // validate incoming request
            $validator = Validator::make($request->data, [
                'exam_shift_code' => 'unique:exam_shifts,exam_shift_code',
            ]);
        } //update
        else if ($request->mode == 2) {
            $validator = Validator::make($request->data, [
                'exam_shift_code' => "required|unique:exam_shifts,exam_shift_code,{$request->data['exam_shift_id']},id",
            ]);
        }

        if ($validator->fails()) {
            $this->sendResponseSuccess(['result' => true]);
        }
        return $this->sendResponseSuccess(['result' => false]);
    }

    /**
     * Thêm mới ca thi
     * @param Request $request
     * @return void
     */
    public function save(Request $request)
    {
        //Thêm mới ca thi
        $this->handlerExamShift($request, FormMode::INSERT);
    }

    /**
     * cập nhật ca thi
     */
    public function update(Request $request)
    {
        $this->handlerExamShift($request, FormMode::UPDATE);
    }

    /**
     * Xóa ca thi
     * @param Request $request
     * @return void
     */
    public function delete(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $examShiftDetail = ExamShiftDetail::whereIn('exam_shift_id', [$request->id]);
                DB::table('exam_results')->whereIn('exam_shift_detail_id', $examShiftDetail->pluck('id')->toArray());
                DB::table('exam_results')->whereIn('exam_shift_detail_id', $examShiftDetail->pluck('id')->toArray());
                ExamShift::find($request->id)->delete();
            });
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
        }
    }

    /**
     * Xử lý thêm mới/cập nhật ca thi
     */
    function handlerExamShift(Request $request, $mode)
    {
        switch ($mode) {
            case FormMode::INSERT:
                $validate = "required|unique:exam_shifts,exam_shift_code";
                break;
            case FormMode::UPDATE:
                $validate = "required|unique:exam_shifts,exam_shift_code,{$request['exam_shift_id']},id";
                break;
            default:
                # code...
                break;
        }
        $attribute = $request->validate([
            'exam_shift_code' => $validate,
            'exam_shift_name' => 'required',
            'exam_bank_id' => 'required',
            'departments' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'note' => '',
            'id' => 'required',
        ],
            [
                'exam_shift_code.required' => 'Mã ca thi không được để trống',
                'exam_shift_code.unique' => 'Mã ca thi đã tồn tại trong hệ thống',
                'exam_shift_name.required' => 'Tên ca thi không được để trống',
                'start_date.required' => 'Ngày bắt đầu không được để trống',
                'end_date.required' => 'Ngày kết thúc không được để trống',
                'departments.required' => 'Phòng thi không được để trống',
                'id.required' => 'Đề thi không được để trống',
            ]);

        $attributeExamShift = [
            'exam_shift_code' => $attribute['exam_shift_code'],
            'exam_shift_name' => $attribute['exam_shift_name'],
            'start_date' => $this->convertDateTime($attribute['start_date']),
            'end_date' => $this->convertDateTime($attribute['end_date']),
            'exam_id' => $attribute['id'],
        ];

        $attributeExamShiftDetail = [];
        foreach ($attribute['departments'] as $departmantId) {
            $objExamShiftDetail['department_id'] = $departmantId;
            //chi tiết đề
            foreach ($attribute['exam_bank_id'] as $examBankId) {
                $objExamShiftDetail['exam_bank_id'] = $examBankId;
                $attributeExamShiftDetail[] = $objExamShiftDetail;
            }
        }
        try {
            DB::transaction(function () use ($attributeExamShift, $attributeExamShiftDetail, $request, $mode) {
                if ($mode == FormMode::UPDATE) {
                    //có sự thay đổi đề thi hoặc phòng thi
                    if ($request->Flag) {
                        $examShiftDetail = ExamShiftDetail::whereIn('exam_shift_id', [$request->exam_shift_id]);
                        $examResult = DB::table('exam_results')->whereIn('exam_shift_detail_id', $examShiftDetail->pluck('id')->toArray());
                        $examResultDetail = DB::table('exam_results')->whereIn('exam_shift_detail_id', $examShiftDetail->pluck('id')->toArray());
                        //xóa kết quả chi tiết của bài thi
                        $examResultDetail->delete();
                        //xóa kết quả chi của bài thi
                        $examResult->delete();
                        //xóa các ca thi chi tiết trong kì thi
                        $examShiftDetail->delete();
                    }
                    ExamShift::find($request->exam_shift_id)->update($attributeExamShift);
                    $examShiftId = $request->exam_shift_id;
                }
                if ($mode == FormMode::INSERT) {
                    //thêm mới ca thi
                    $examShiftId = ExamShift::insertGetId($attributeExamShift);
                }

                foreach ($attributeExamShiftDetail as $key => $value) {
                    $attributeExamShiftDetail[$key]['exam_shift_id'] = $examShiftId;
                }
                examshiftdetail::insert($attributeExamShiftDetail);

            });
        } catch (\Exception $e) {
            dd($e);
            \Log::debug($e->getMessage());
        }

    }
}
