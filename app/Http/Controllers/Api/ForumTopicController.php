<?php

namespace App\Http\Controllers\Api;

use App\Models\ForumTopic;
use App\Traits\HasCommunityPermissions;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ForumTopicController extends Controller
{
    use HasCommunityPermissions, LogsAuditTrail;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $query = ForumTopic::with(['user', 'community']);
        
        // Filter by community if specified
        if ($request->has('community_id')) {
            $query->where('community_id', $request->community_id);
        }
        
        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'community_id' => 'required|exists:communities,id',
            'is_announcement' => 'boolean'
        ]);

        // Check if user can create forum posts in this community
        $permission = $request->boolean('is_announcement') ? 'send_announcements' : 'moderate_content';
        $permissionError = $this->requireCommunityPermission($user, $validatedData['community_id'], $permission);
        if ($permissionError) {
            $this->logPermissionAction('forum_topic_create', $validatedData['community_id'], $permission, false, null, $request);
            return $permissionError;
        }

        $validatedData['user_id'] = $user->id;
        $topic = ForumTopic::create($validatedData);

        // Log the action
        $this->logAudit(
            'forum_topic_created',
            $validatedData['community_id'],
            'ForumTopic',
            $topic->id,
            null,
            $topic->toArray(),
            "Created forum topic: {$topic->title}",
            $request
        );

        return response()->json($topic->load(['user', 'community']), 201);
    }

    public function show($id)
    {
        return ForumTopic::with(['user', 'community'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $topic = ForumTopic::findOrFail($id);
        
        // Check if user can moderate content in this community
        $permissionError = $this->requireCommunityPermission($user, $topic->community_id, 'moderate_content');
        if ($permissionError) {
            $this->logPermissionAction('forum_topic_update', $topic->community_id, 'moderate_content', false, 
                ['topic_id' => $topic->id], $request);
            return $permissionError;
        }

        $oldValues = $topic->toArray();
        
        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'is_announcement' => 'sometimes|boolean'
        ]);

        $topic->update($validatedData);

        // Log the action
        $this->logAudit(
            'forum_topic_updated',
            $topic->community_id,
            'ForumTopic',
            $topic->id,
            $oldValues,
            $topic->fresh()->toArray(),
            "Updated forum topic: {$topic->title}",
            $request
        );

        return $topic->fresh()->load(['user', 'community']);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $topic = ForumTopic::findOrFail($id);
        
        // Check if user can moderate content in this community
        $permissionError = $this->requireCommunityPermission($user, $topic->community_id, 'moderate_content');
        if ($permissionError) {
            $this->logPermissionAction('forum_topic_delete', $topic->community_id, 'moderate_content', false, 
                ['topic_id' => $topic->id], $request);
            return $permissionError;
        }

        $topicData = $topic->toArray();

        // Log the action before deletion
        $this->logAudit(
            'forum_topic_deleted',
            $topic->community_id,
            'ForumTopic',
            $topic->id,
            $topicData,
            null,
            "Deleted forum topic: {$topic->title}",
            $request
        );

        $topic->delete();
        
        return response()->json(['message' => 'Forum topic deleted successfully']);
    }
}
