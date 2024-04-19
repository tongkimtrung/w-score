<?php

namespace App\Http\Controllers;

use App\Models\ExamResult;
use App\Models\ExamResultDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiExamResultController extends Controller
{
    public function getExamResultDetail(Request $request)
    {
        $request->validate([
            'student_code' => 'required',
            'exam_shift_detail_id' => 'required|integer',
        ]);

        $ret = ExamResultDetail::where('exam_shift_detail_id', $request->exam_shift_detail_id)->where('student_code', $request->student_code)->where('has_child', false)->get()->toArray();
        return $this->sendResponseSuccess($ret);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getExamResult(Request $request)
    {
        $request->validate([
            'examId' => 'required|integer',
            'departmentId' => 'required|integer',
            'examShiftId' => 'required|integer',
        ]);

        $ret = DB::table('exam_results')
            ->join('exam_shift_details', 'exam_shift_details.id', '=', 'exam_results.exam_shift_detail_id')
            ->join('exam_shifts', 'exam_shifts.id', '=', 'exam_shift_details.exam_shift_id')
            ->join('exam_banks', 'exam_banks.id', '=', 'exam_shift_details.exam_bank_id')
            ->where('exam_shift_details.exam_shift_id', '=', $request->examShiftId)
            ->where('exam_shift_details.department_id', '=', $request->examId)
            ->where('exam_shifts.exam_id', '=', $request->examId)
            ->get()->toArray();
        return $this->sendResponseSuccess($ret);
    }
}
