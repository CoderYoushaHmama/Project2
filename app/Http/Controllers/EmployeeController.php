<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PersonRequest;
use App\Http\Resources\EmployeeCollection;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    //Login Function
    public function login(LoginRequest $request)
    {
        $user = User::where(['is_admin' => 0, 'username' => $request->username])->first();

        if (!$user) {
            return error("This User Not Found", null, 404);
        }

        if (Hash::check($request->password, $user->password)) {
            $token = $user->createToken('employee')->plainTextToken;

            return success($token, 'login successfully');
        }

        return error('incorrect password', null, 502);
    }

    //Get Profile Function
    public function profile()
    {
        $employee = Auth::guard('user')->user();
        $employee->person;
        $employee->person->employee;
        return success($employee, null);
    }

    //Add Employee Function
    public function addEmployee(EmployeeRequest $employeeRequest, PersonRequest $personRequest)
    {
        $person = Person::create([
            'name' => $personRequest->name,
            'phone_number' => $personRequest->phone_number,
            'birth_date' => $personRequest->birth_date,
            'type' => 'E',
        ]);

        $employee = Employee::create([
            'person_id' => $person->id,
            'shift_id' => $employeeRequest->shift_id,
            'job_id' => $employeeRequest->job_title_id,
            'account_id' => $employeeRequest->user_id,
            'credentials' => $employeeRequest->credentials,
        ]);
     
        return success(null, 'this employee added successfully', 201);
    }

    //Edit Employee Function
    public function editEmployee(Employee $employee, EmployeeRequest $employeeRequest, PersonRequest $personRequest)
    {
        $employee->person()->update([
            'name' => $personRequest->name,
            'phone_number' => $personRequest->phone_number,
            'birth_date' => $personRequest->birth_date,
        ]);

    
        $employee->update([
            'role_id' => $employeeRequest->role_id,
            'job_id' => $employeeRequest->job_title_id,
            'credentials' => $employeeRequest->credentials,
            'shift_id' => $employeeRequest->shift_id
        ]);

        return success(null, 'this employee been edited successfully');
    }

    //Get Employees Function
    public function getEmployees()
    {
        $employees = Employee::with('person', 'shift', 'user',"jobTitle")->paginate(20);
        return (new EmployeeCollection($employees));
    }

    //Get Employee Information Function
    public function getEmployeeInformation(Employee $employee)
    {
        $employee = $employee->with(['person', 'user','shift'])->find($employee->id);
        return success(new EmployeeResource($employee), null);
    }
    public function getNames(){
        $employees = Employee::with('person', 'shift', 'user',"jobTitle")->get();
    }

    //Delete Employee Function
    public function deleteEmployee(Employee $employee)
    {
        if ($employee->person->user) {
            $employee->person->user->delete();
        }
        
        $employee->person->delete();
        $employee->delete();
        return success(null, 'this employee deleted successfully',204);
    }

  
}