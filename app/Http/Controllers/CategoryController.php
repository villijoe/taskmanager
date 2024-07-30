<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * @group Category
     * 
     * Get list of categories
     * 
     * @response 200 [
     *  {
     *      "id": 1,
     *      "name": "Срочные задачи",
     *      "type": "standard",
     *      "created_at": "2023-07-28T12:00:00.000000Z",
     *      "updated_at": "2023-07-28T12:00:00.000000Z"
     *  },
     *  {
     *      "id": 2,
     *      "name": "Плановые задачи",
     *      "type": "normal",
     *      "created_at": "2023-07-28T12:00:00.000000Z",
     *      "updated_at": "2023-07-28T12:00:00.000000Z"
     *  }
     * ]
     */
    public function index()
    {
        $categories = Category::where('user_id', Auth::id())->orWhere('is_default', true)->get();
        return response()->json($categories);
    }

    /**
     * @group Category
     * 
     * Create a new category
     * 
     * @bodyParam name string required The name of the category. Example: Плановые задачи
     * @bodyParam type string required The type of the category. Example: normal
     * 
     * @response 201 {
     *  "id": 2,
     *  "name": "Плановые задачи",
     *  "type": "normal",
     *  "created_at": "2023-07-28T12:00:00.000000Z",
     *  "updated_at": "2023-07-28T12:00:00.000000Z"
     * }
     */
    public function store(Request $request)
    {

        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = Category::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'user_id' => Auth::id()
        ]);

        return response()->json($category, 201);
    }

    /**
     * @group Category
     * 
     * Get category by ID
     * 
     * @urlParam category integer required The ID of the category. Example: 1
     * 
     * @response 200 {
     *  "id": 1,
     *  "name": "Срочные задачи",
     *  "type": "standard",
     *  "created_at": "2023-07-28T12:00:00.000000Z",
     *  "updated_at": "2023-07-28T12:00:00.000000Z"
     * }
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);

        if (Gate::denies('view', $category)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($category); 
    }

    /**
     * @group Category
     * 
     * Update an existing category
     * 
     * @urlParam id integer required The ID of the category. Example: 1
     * @bodyParam name string required The name of the category. Example: Обновленные задачи
     * @bodyParam type string required The type of the category. Example: normal
     * 
     * @response 200 {
     *  "id": 1,
     *  "name": "Обновленные задачи",
     *  "type": "normal",
     *  "created_at": "2023-07-28T12:00:00.000000Z",
     *  "updated_at": "2023-07-28T12:00:00.000000Z"
     * }
     * 
     * @response 403 {
     *  "message": "Cannot modify this category"
     * }
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        if (Gate::denies('update', $category)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category->update($data);

        return response()->json($category);
    }

    /**
     * @group Category
     * 
     * Delete a category
     * 
     * @urlParam category integer required The ID of the category. Example: 1
     * 
     * @response 204 {
     *  "message": "Category deleted"
     * }
     * 
     * @response 403 {
     *  "message": "Cannot delete this category"
     * }
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if (Gate::denies('delete', $category)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
