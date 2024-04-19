<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamBank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiDashBoardController extends Controller
{
    public function index()
    {
        $ret = [
            'countUser' => User::count(),
            'countDepartment' => Department::count(),
            'countExam' => Exam::count(),
            'countExamBank' => ExamBank::count(),
            'bankStatistics' => [
                ExamBank::doesntHave('criterias')->count(),
                ExamBank::has('criterias')->count(),
                ExamBank::doesntHave('departments')->count(),
                ExamBank::has('departments')->count(),
            ],
            'examStatistics' => DB::table('exam_results')
                ->select(
                    DB::raw('COUNT(CASE WHEN total BETWEEN 0 AND 5 THEN 1 END) AS score_range_0_5'),
                    DB::raw('COUNT(CASE WHEN total BETWEEN 5 AND 6.5 THEN 1 END) AS score_range_5_6_5'),
                    DB::raw('COUNT(CASE WHEN total BETWEEN 6.5 AND 8 THEN 1 END) AS score_range_6_5_8'),
                    DB::raw('COUNT(CASE WHEN total BETWEEN 8 AND 9 THEN 1 END) AS score_range_8_9'),
                    DB::raw('COUNT(CASE WHEN total BETWEEN 9 AND 10 THEN 1 END) AS score_range_9_10')
                )
                ->first()
        ];
        return $this->sendResponseSuccess($ret);
    }
}
