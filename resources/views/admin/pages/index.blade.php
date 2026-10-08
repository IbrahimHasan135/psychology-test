@extends('layouts.app', ['title' => 'Page Management'])

@section('content')
<div class="app-layout">
    @include('admin.partials.sidebar')
    <main class="content">
        @include('admin.partials.topbar', [
            'breadcrumb' => 'Page Management',
            'title' => 'Page Management',
            'description' => 'Manage website content from sections, tabs, and visual cards.',
        ])

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Slug</th>
                        <th>Mode</th>
                        <th>Sections</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        <tr>
                            <td>{{ $page->name }}</td>
                            <td>{{ $page->slug }}</td>
                            <td><span class="pill">{{ $page->display_mode }}</span></td>
                            <td>{{ $page->sections_count }}</td>
                            <td>{{ $page->is_published ? 'Published' : 'Draft' }}</td>
                            <td><a class="button button-soft" href="{{ route('admin.pages.edit', $page) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection
