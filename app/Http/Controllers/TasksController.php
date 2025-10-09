<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TasksController extends Controller
{

    public function index()
    {
        $tasks = Task::query()
            ->where('user_id', auth()->user()->id)
            ->get()
            ->toArray();

        return response()->json([
            'data' => $tasks,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => auth()->user()->id,
            'status' => 'pending',
        ];
        $task = Task::query()->create($data);
        return response()->json([
            'data' => $task,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $task = Task::query()
            ->where('user_id', auth()->user()->id)
            ->findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:pending,completed',
        ]);

        $task->update($validatedData);

        return response()->json([
            'data' => $task,
        ]);
    }

    public function destroy(string $id)
    {
        $task = Task::query()
            ->where('user_id', auth()->user()->id)
            ->findOrFail($id);

        $task->delete();
        return response()->json([], 204);
    }

    public function show(string $id)
    {
        $task = Task::query()
            ->where('user_id', auth()->user()->id)
            ->findOrFail($id)
            ->toArray();

        return response()->json([
            'data' => $task,
        ]);
    }

}
