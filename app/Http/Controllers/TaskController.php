<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;


class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @group Task
     * 
     * Get list of tasks
     *
     * Retrieve a list of all tasks. You can filter tasks by category.
     *
     * @queryParam category_id integer Filter tasks by category ID. Example: 1
     * 
     * @response 200 {
     *   "id": 1,
     *   "title": "Finish Project",
     *   "description": "Complete the project by end of the week.",
     *   "due_date": "2024-08-05",
     *   "category_id": 2,
     *   "user_id": 1,
     *   "status": "IN_PROGRESS"
     * }
     */
    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');
        $tasks = Task::where('user_id', Auth::id())
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })->get();

        return response()->json($tasks);
    }
    
    /**
     * @group Task
     * 
     * Create a new task
     *
     * @bodyParam title string required The title of the task. Example: Finish Project
     * @bodyParam description string The description of the task. Example: Complete the project by end of the week.
     * @bodyParam due_date date required The due date of the task. Example: 2024-08-05
     * @bodyParam category_id integer required The ID of the category. Example: 2
     * 
     * @response 201 {
     *   "id": 1,
     *   "title": "Finish Project",
     *   "description": "Complete the project by end of the week.",
     *   "due_date": "2024-08-05",
     *   "category_id": 2,
     *   "user_id": 1,
     *   "status": "IN_PROGRESS"
     * }
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
        ]);

        $task = Task::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'due_date' => $validatedData['due_date'],
            'category_id' => $validatedData['category_id'],
            'user_id' => Auth::id(),
        ]);

        return response()->json($task, 201);
    }


    /**
     * @group Task
     * 
     * Get a specific task
     *
     * @urlParam task integer required The ID of the task. Example: 1
     * 
     * @response 200 {
     *   "id": 1,
     *   "title": "Finish Project",
     *   "description": "Complete the project by end of the week.",
     *   "due_date": "2024-08-05",
     *   "category_id": 2,
     *   "user_id": 1,
     *   "status": "IN_PROGRESS"
     * }
     */
    public function show($id)
    {
        $task = Task::findOrFail($id);

        if (Gate::denies('view', $task)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($task);
    }
    
    /**
     * @group Task
     * 
     * Update a task
     *
     * @urlParam id integer required The ID of the task. Example: 1
     * @bodyParam title string The title of the task. Example: Finish Project
     * @bodyParam description string The description of the task. Example: Complete the project by end of the week.
     * @bodyParam due_date date The due date of the task. Example: 2024-08-05
     * @bodyParam category_id integer The ID of the category. Example: 2
     * 
     * @response 200 {
     *   "id": 1,
     *   "title": "Finish Project",
     *   "description": "Complete the project by end of the week.",
     *   "due_date": "2024-08-05",
     *   "category_id": 2,
     *   "user_id": 1,
     *   "status": "IN_PROGRESS"
     * }
     * 
     * @response 403 {
     *   "message": "Cannot modify this task"
     * }
     * 
     * @response 422 {
     *  "message": "Validation error"
     * }
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'category_id' => 'required|exists:categories,id',
        ]);

        $task->update($validated);
        return response()->json($task);
    }
    
    /**
     * @group Task
     * 
     * Delete a task
     *
     * @urlParam task integer required The ID of the task. Example: 1
     * 
     * @response 204 {
     *  "message": "Task deleted"
     * }
     * 
     * @response 403 {
     *  "message": "Cannot delete this task"
     * }
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();
        return response()->json(null, 204);
    }
    
    /**
     * @group Task
     * 
     * Get all tasks for each user
     *
     * Retrieve a list of all tasks.
     * 
     * @response 200 {
     *   "id": 3,
     *   "name": "John Doe",
     *   "email": "john1@example.com",
     *   "email_verified_at": null,
     *   "created_at": "2024-07-29T12:24:03.000000Z",
     *   "updated_at": "2024-07-29T12:24:03.000000Z",
     *   "role_id": 2,
     *   "tasks_count": 1
     * }
     */
    public function adminIndex()
    {
        $users = User::whereHas('tasks')->withCount('tasks')->get(['email', 'tasks_count']);
        return response()->json($users);
    }
    
    /**
     * @group Task
     * 
     * Get all tasks for selected user
     *
     * Retrieve a list of all tasks.
     *
     * @urlParam user integer required The ID of the user. Example: 1
     * 
     * @response 200 {
     *   "email": "john1@example.com",
     *   "categories": [
     *       {
     *           "category_name": "Срочные задачи",
     *           "task_count": 1
     *       }
     *   ]
     * }
     * 
     * @response 404 {
     *  "message": "Task not found"
     * }
     */
    public function adminShowUserTasks(User $user)
    {
        $categories = $user->tasks()
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($tasks) {
                return [
                    'category_name' => $tasks->first()->category->name,
                    'task_count' => $tasks->count()
                ];
            })
            ->values();

        return response()->json([
            'email' => $user->email,
            'categories' => $categories
        ]);
    }
}
