<div class="mb-4">
    <label class="block">URL</label>
    <input type="text" name="url" value="{{ old('url', $feedUrl->url ?? '') }}" class="border rounded px-4 py-2 w-full" required>
</div>
<div class="mb-4">
    <label class="block">タグ</label>
    <input type="text" name="tag" value="{{ old('tag', $feedUrl->tag ?? '') }}" class="border rounded px-4 py-2 w-full">
</div>
<div class="mb-4">
    <label class="block">タイプ</label>
    <select name="type" class="border rounded px-4 py-2 w-full">
        @foreach(['rss', 'google_alert', 'crawl'] as $type)
        <option value="{{ $type }}" {{ old('type', $feedUrl->type ?? '') == $type ? 'selected' : '' }}>
            {{ strtoupper($type) }}
        </option>
        @endforeach
    </select>
</div>