<?php

namespace Amghrby\Workflows\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Amghrby\Workflows\Workflow;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WorkflowController extends Controller
{
    private const WORKFLOW_NOT_FOUND_MESSAGE = 'Workflow not found.';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $workflows = Workflow::paginate(
                perPage: $request->input('per_page', 10),
                page: $request->input('page', 1)
            );
            return response()->json(array_merge(
                [
                    'message' => 'Workflows retrieved successfully',
                ],
                $workflows->toArray()
            ));
        } catch (\Exception $e) {
            Log::error('Failed to retrieve workflows.' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return response()->json(['message' => 'An error occurred', 'errors' => [$e->getMessage()]], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|unique:' . (new Workflow())->getTable() . '|max:255',
            ]);

            $workflow = Workflow::create($validatedData);

            return response()->json([
                'message' => 'Workflow created successfully',
                'data' => $workflow,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create workflow.' . $e->getMessage() .' '. $e->getTraceAsString());
            return response()->json(['message' => 'An error occurred', 'errors' => [$e->getMessage()]], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $workflow = Workflow::findOrFail($id);

            return response()->json([
                'message' => 'Workflow retrieved successfully',
                'data' => $workflow,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => self::WORKFLOW_NOT_FOUND_MESSAGE, 'errors' => []], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve workflow.' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return response()->json(['message' => 'Failed to retrieve workflow.', 'errors' => []], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'sometimes|unique:' . (new Workflow())->getTable() . '|max:255',
            ]);

            $workflow = Workflow::findOrFail($id);
            $workflow->update($request->all());

            $response = ['message' => 'Workflow updated successfully', 'data' => $workflow];
            $status = 200;
        } catch (ValidationException $e) {
            $response = ['message' => 'Validation failed', 'errors' => $e->validator->errors()->all()];
            $status = 422;
        } catch (ModelNotFoundException $e) {
            $response = ['message' => self::WORKFLOW_NOT_FOUND_MESSAGE, 'errors' => []];
            $status = 404;
        } catch (\Exception $e) {
            Log::error('Failed to update workflow.' . $e->getMessage() . ' ' . $e->getTraceAsString());

            $response = ['message' => 'Failed to update workflow.', 'errors' => []];
            $status = 500;
        }

        return response()->json($response, $status);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $workflow = Workflow::findOrFail($id);
            $workflow->delete();

            return response()->json(['message' => 'Workflow deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => self::WORKFLOW_NOT_FOUND_MESSAGE], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete workflow.' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return response()->json(['error' => 'Could not delete workflow.'], 500);
        }
    }

    public function addTrigger($id, Request $request)
    {


        try {
            $workflow = Workflow::findOrFail($id);

            if (array_key_exists($request->name, config('workflows.triggers.types'))) {
                $trigger = config('workflows.triggers.types')[$request->name]::create([
                    'type' => config('workflows.triggers.types')[$request->name],
                    'workflow_id' => $workflow->id,
                    'name' => $request->name,
                    'data_fields' => null,
                ]);

                return response()->json(
                    [
                        'message' => 'Trigger created successfully',
                        'trigger' => $trigger,
                    ],
                    201,
                );
            }

            return response()->json(['error' => 'Trigger type not found.'], 404);
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => self::WORKFLOW_NOT_FOUND_MESSAGE], 404);
        } catch (\Exception $e) {
            Log::error('Failed to attach trigger to workflow.' . $e->getMessage() .' '. $e->getTraceAsString());
            return response()->json(['error' => 'Could not attach trigger to workflow.'], 500);
        }
    }
}
