<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Получить список задач.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = Task::query();

        // Поиск по названию
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Сортировка
        if ($request->has('sort')) {
            $query->orderBy($request->sort);
        }

        // Пагинация
        $tasks = $query->paginate(10);

        // Возвращаем данные через TaskResource
        return TaskResource::collection($tasks);
    }

    /**
     * Создать новую задачу.
     *
     * @param Request $request
     * @return TaskResource
     */
    public function store(Request $request)
    {
        // Валидация данных
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'nullable|string|in:низкий,средний,высокий',
            'category' => 'nullable|string',
            'status' => 'nullable|string|in:выполнена,не выполнена',
        ]);

        // Создание задачи
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'create_date' => now(),
            'priority' => $request->priority ?? 'низкий',
            'category' => $request->category,
            'status' => $request->status ?? 'не выполнена',
        ]);

        // Возвращаем созданную задачу через TaskResource
        return new TaskResource($task);
    }

    /**
     * Получить задачу по ID.
     *
     * @param int $id
     * @return TaskResource
     */
    public function show($id)
    {
        $task = Task::findOrFail($id);
        return new TaskResource($task);
    }

    /**
     * Обновить задачу.
     *
     * @param Request $request
     * @param int $id
     * @return TaskResource
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Валидация данных
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'priority' => 'nullable|string|in:низкий,средний,высокий',
            'category' => 'nullable|string',
            'status' => 'nullable|string|in:выполнена,не выполнена',
        ]);

        // Обновление задачи
        $task->update($request->all());

        // Возвращаем обновленную задачу через TaskResource
        return new TaskResource($task);
    }

    /**
     * Удалить задачу.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
