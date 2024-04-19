<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class ApiDepartmentController extends Controller
{
    public function save(Request $request)
    {
        $attribute = $request->validate([
            'department_name' => 'required',
            'department_code' => 'required|unique:departments,department_code'
        ],
            [
                'department_name.required' => 'Tên phòng thi không được để trống',
                'department_code.required' => 'Mã phòng thi không được để trống',
                'department_code.unique' => 'Mã phòng thi đã tồn tại',
            ]);

        Department::insert($attribute);
    }


    public function update(Request $request, $id)
    {
        $attribute = $request->validate([
            'department_name' => 'required',
            'department_code' => "required|unique:departments,department_code,{$id}",
        ],
            [
                'department_name.required' => 'Tên phòng thi không được để trống',
                'department_code.required' => 'Mã phòng thi không được để trống',
                'department_code.unique' => 'Mã phòng thi đã tồn tại',
            ]);
        department::where('id', $id)->update($attribute);
    }

    public function get()
    {
        return $this->sendResponseSuccess(Department::with('examShifts')->get()->toArray());
    }

    /**
     * Xóa phòng thi
     * @return void
     */
    public function delete($id)
    {
        Department::where('id', $id)->delete();
    }
}
