<x-admin.wrapper>
    <x-slot name="title">
        {{ __('Project Data') }}
    </x-slot>

    <div class="py-2 px-4">
        <form method="GET" action="{{ route('admin.view.project.data') }}" class="flex flex-wrap items-center gap-3 mb-4">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Search project or developer..." 
                class="input input-bordered w-full sm:w-64" 
            />

            <select name="state" class="select select-bordered w-full sm:w-48">
                <option value="">All States</option>
                @foreach($states as $state)
                    <option value="{{ $state }}" {{ strtolower(request('state')) == strtolower($state) ? 'selected' : '' }}>
                        {{ ucwords($state) }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <div class="w-full overflow-x-auto border border-base-200 shadow rounded-lg">
            <table class="table w-full table-zebra text-sm">
                <thead>
                    <tr class="bg-base-200 text-left">
                        <th>ID</th>
                        <th>Project Code</th>
                        <th>Project Name</th>
                        <th>Developer Name</th>
                        <th>License No.</th>
                        <th>District</th>
                        <th>State</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>{{ $project->id }}</td>
                            <td>{{ $project->project_code }}</td>
                            <td>{{ $project->project_name }}</td>
                            <td>{{ $project->developer_name }}</td>
                            <td>{{ $project->license_number }}</td>
                            <td>{{ $project->district }}</td>
                            <td>{{ $project->state }}</td>
                            <td>{{ $project->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.view.project.data.show', $project->id) }}" class="text-blue-600 underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">No Project Data Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="py-4">
            {{ $projects->appends(request()->query())->links() }}
        </div>
    </div>
</x-admin.wrapper>