<div class="tab-pane fade" id="bugUpdateSetting" role="tabpanel" aria-labelledby="bug-update-setting">
    <div class="card">
        <h4 class="card-header p-3"><b>@lang('file.Bug Setting')</b></h4>
        <hr>
        <div class="card-body">
            <form action="{{ route('bug-update-setting.submit') }}" method="POST">
                @csrf

                <!----------------------------------- Files ------------------------------------------>

                <h5><b>@lang('Files')</b></h5>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-8">
                        <div class="filesArea">
                            @if (isset($bugSettings->files))
                                @foreach ($bugSettings->files as $item)
                                    <div class="row">
                                        <div class="col-8 form-group">
                                            <label>{{__('file.File Name')}}</label>
                                            <input value="{{ $item->file_name }}" type="text" name="file_name[]" class="form-control" placeholder="{{__('file.Type File Name')}}">
                                        </div>
                                        <div class="form-group">
                                            <label>@lang('file.Delete')</label><br>
                                            <span class="btn btn-default btn-sm del-row"><i class="dripicons-trash"></i></span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row">
                                    <div class="col-8 form-group">
                                        <label>{{__('file.File Name')}}</label>
                                        <input type="text" name="file_name[]" class="form-control" placeholder="{{__('file.Type File Name')}}">
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('file.Delete')</label><br>
                                        <span class="btn btn-default btn-sm del-row"><i class="dripicons-trash"></i></span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <span class="btn btn-link add-more" id="addMoreFile"><i class="dripicons-plus"></i> @lang('file.Add More')</span>
                    </div>
                </div>

                <!----------------------------------- Change Log ------------------------------------------>
                <hr>
                <h5><b>@lang('file.Logs')</b></h5>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-8">
                        <div class="logArea">
                            @if (isset($bugSettings->logs))
                                @foreach ($bugSettings->logs as $item)
                                    <div class="row">
                                        <div class="col-8 form-group">
                                            <label>{{__('file.Type Log')}}</label>
                                            <input value="{{ $item->text }}" type="text" name="text[]" class="form-control" placeholder="{{__('file.Type Log')}}">
                                        </div>
                                        <div class="form-group">
                                            <label>@lang('file.Delete')</label><br>
                                            <span class="btn btn-default btn-sm del-row-log"><i class="dripicons-trash"></i></span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row">
                                    <div class="col-8 form-group">
                                        <label>{{__('file.Type Log')}}</label>
                                        <input type="text" name="text[]" class="form-control" placeholder="{{__('file.Type Log')}}">
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('file.Delete')</label><br>
                                        <span class="btn btn-default btn-sm del-row-log"><i class="dripicons-trash"></i></span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <span class="btn btn-link add-more" id="addMoreLog"><i class="dripicons-plus"></i> @lang('file.Add More')</span>
                    </div>
                </div>

                <!----------------------------------- Short Note ------------------------------------------>
                <hr>
                <h5><b>@lang('file.Short Note')</b></h5>
                <hr>
                <div class="form-group row">
                    <div class="col-md-12">
                        @if (isset($bugSettings->short_note))
                            <textarea name="short_note" class="form-control" rows="5">{{ $bugSettings->short_note }}</textarea>
                        @else
                            <textarea name="short_note" class="form-control" rows="5"></textarea>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">@lang('file.Submit')</button>
                </div>
            </form>
        </div>
    </div>
</div>
