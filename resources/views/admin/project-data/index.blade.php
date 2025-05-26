<x-admin.wrapper>
    <x-slot name="title">
        {{ __('Project Data') }}
    </x-slot>

    <div class="py-2">
        <div class="min-w-full border-base-200 shadow overflow-x-auto">
            <table class="table w-full">
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
                            <td colspan="8" class="text-center py-4">No Project Data Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="py-4">
            {{ $projects->links() }}
        </div>
    </div>
</x-admin.wrapper>
