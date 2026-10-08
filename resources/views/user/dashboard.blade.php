@extends('layouts.app', ['title' => 'User Dashboard'])

@section('content')
<main class="page section">
    <div class="split">
        <div>
            <div class="eyebrow">User Area</div>
            <h2>Hello, {{ $user->name }}</h2>
            <p>This is the starting area for user-facing features such as tasks, results, or profile updates.</p>
        </div>
        <span class="pill">{{ \App\Enums\UserRole::label($user->role) }}</span>
    </div>

    <div class="grid-3">
        <article class="card">
            <h3>Tasks</h3>
            <p>Future user workflows can be mounted here.</p>
        </article>
        <article class="card">
            <h3>Results</h3>
            <p>Summary and history modules can be shown here.</p>
        </article>
        <article class="card">
            <h3>Profile</h3>
            <p>Personal account settings can be managed here.</p>
        </article>
    </div>
</main>
@endsection
