<div class="form-grid">
    <div>
        <label>Card Template</label>
        <select name="template">
            @foreach ($templates as $value => $label)
                <option value="{{ $value }}" @selected(old('template', $card?->template ?? 'feature') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Image Position</label>
        <select name="image_position">
            @foreach ($imagePositions as $value => $label)
                <option value="{{ $value }}" @selected(old('image_position', $card?->image_position ?? 'left') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Title</label>
        <input name="title" value="{{ old('title', $card?->title) }}" required>
    </div>
    <div>
        <label>Order</label>
        <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $card?->sort_order ?? 0) }}">
    </div>
    <div class="form-wide">
        <label>Body Copy</label>
        <textarea name="body" rows="3">{{ old('body', $card?->body) }}</textarea>
    </div>
    <div class="form-wide">
        <label>Image URL</label>
        <input name="image_url" value="{{ old('image_url', $card?->image_url) }}" placeholder="https://... or /images/file.svg">
    </div>
    <div>
        <label>Button Label</label>
        <input name="button_label" value="{{ old('button_label', $card?->button_label) }}">
    </div>
    <div>
        <label>Button URL</label>
        <input name="button_url" value="{{ old('button_url', $card?->button_url) }}">
    </div>
    <label class="checkbox-row compact-check">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $card?->is_active ?? true))>
        <span>Active</span>
    </label>
</div>
