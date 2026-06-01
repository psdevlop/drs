<a href="{{ route('evaluations.show', $eval) }}" class="btn btn-sm btn-outline">{{ __('messages.view') }}</a>
@if(auth()->user()->isAdmin() && !$eval->isConfirmed())
    <form method="POST" action="{{ route('evaluations.confirm', $eval) }}" style="display:inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-success">{{ __('messages.confirm') }}</button>
    </form>
@endif
@if($eval->evaluator_id === auth()->id() || auth()->user()->isSuperAdmin())
    <a href="{{ route('evaluations.edit', $eval) }}" class="btn btn-sm btn-primary">{{ __('messages.edit') }}</a>
@endif
<form method="POST" action="{{ route('evaluations.destroy', $eval) }}" style="display:inline" onsubmit="return confirm('{{ __('messages.delete_evaluation_short_confirm') }}');">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.delete') }}</button>
</form>
