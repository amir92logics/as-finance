<!-- Card -->
<div id="usernameSection" class="card">
    <div class="card-header">
        <h4 class="card-title">@lang('Update Badge')</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.badgeUpdate', $user->id) }}" method="post">
            @csrf
            <div class="row mb-4">
                <label for="" class="col-sm-3 col-form-label form-label">
                    @lang('Select Badge')
                </label>
                <div class="col-sm-9">
                    <div class="tom-select-custom">
                        <select class="js-select form-select" name="badge_id"
                                data-hs-tom-select-options='{
                                  "searchInDropdown": false
                                }'>
                            @forelse($badges as $badge)
                                <option value="{{ $badge->id }}" {{ optional($user->rank)->id == $badge->id ? 'selected' : '' }}
                                data-option-template='<span class="d-flex align-items-center"><span>{{ $badge->rank_lavel }}</span></span>'>
                                    {{ $badge->rank_lavel }}
                                </option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                    @error('username')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="d-flex justify-content-start">
                <button type="submit" class="btn btn-primary">@lang('Save changes')</button>
            </div>
        </form>
    </div>
</div>
