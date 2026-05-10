<a href="{{ route('evaluations.show', $eval) }}" class="btn btn-sm btn-outline">View</a>
@if(auth()->user()->isAdmin() && !$eval->isConfirmed())
    <form method="POST" action="{{ route('evaluations.confirm', $eval) }}" style="display:inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-success">Confirm</button>
    </form>
@endif
@if($eval->evaluator_id === auth()->id() || auth()->user()->isSuperAdmin())
    <a href="{{ route('evaluations.edit', $eval) }}" class="btn btn-sm btn-primary">Edit</a>
@endif
<form method="POST" action="{{ route('evaluations.destroy', $eval) }}" style="display:inline" onsubmit="return confirm('Delete this evaluation?');">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
</form>
