@extends('admin.layouts.app')

@section('page_title', 'New Block')

@section('page_content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <form method="POST" action="{{ route('admin.global-blocks.store') }}">
                @csrf
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Block Details</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Block Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" required class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Homepage Hero Banner">
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control">
                                <option value="">— Uncategorized —</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="d-block">Block Type <span class="text-danger">*</span></label>
                            <div class="row">
                                @foreach ($sectionTypes as $sectionType)
                                    <div class="col-6 col-md-4 col-lg-3 mb-2">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" name="type" id="type_{{ $sectionType->key() }}"
                                                   value="{{ $sectionType->key() }}" class="custom-control-input"
                                                   @checked(old('type') === $sectionType->key()) required>
                                            <label class="custom-control-label" for="type_{{ $sectionType->key() }}">
                                                <i class="{{ $sectionType->icon() }} fa-fw mr-1"></i>{{ $sectionType->label() }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('type') <span class="text-danger d-block small mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-arrow-right mr-1"></i> Continue to Content</button>
                        <a href="{{ route('admin.global-blocks.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
