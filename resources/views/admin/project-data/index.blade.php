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
            <select name="state" class="input input-bordered w-full sm:w-64">
                <option value="">All States</option>
                @foreach($states as $state)
                    <option value="{{ $state }}" {{ strtolower(request('state')) == strtolower($state) ? 'selected' : '' }}>
                        {{ ucwords($state) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <button type="button" class="btn btn-secondary" onclick="openColumnOrderModal()">Reorder Columns</button>
        </form>

        <div class="w-full overflow-x-auto border border-base-200 shadow rounded-lg">
            <table class="table w-full table-zebra text-sm">
                <thead>
                    <tr class="bg-base-200 text-left">
                        @php
                            $customColumnLabels = [
                                'new_vp_date' => 'New First VP Date',
                                // Add more custom labels here if needed
                            ];
                        @endphp

                        @foreach($columnOrder as $column)
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort_by' => $column, 'sort_order' => (request('sort_by') == $column && request('sort_order') == 'asc') ? 'desc' : 'asc']) }}">
                                    {{ $customColumnLabels[$column] ?? ucwords(str_replace('_', ' ', $column)) }}
                                    @if(request('sort_by') == $column)
                                        {!! request('sort_order') == 'asc' ? '&uarr;' : '&darr;' !!}
                                    @endif
                                </a>
                            </th>
                        @endforeach

                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            @foreach($columnOrder as $column)
                                <td>{{ $project->$column }}</td>
                            @endforeach
                            <td>
                                <a href="{{ route('admin.view.project.data.show', $project->id) }}" class="text-blue-600 underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columnOrder) + 1 }}" class="text-center py-4">No Project Data Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="py-4">
            {{ $projects->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div id="columnOrderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-full max-w-md shadow-lg max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold mb-4">Reorder & Select Columns</h3>

            <form id="columnOrderForm">
                <ul id="sortableColumns" class="space-y-2">
                    @foreach($allColumns as $column)
                        <li class="flex items-center gap-2 p-2 bg-gray-100 rounded cursor-move" data-column="{{ $column }}">
                            <input type="checkbox" class="column-checkbox" value="{{ $column }}" 
                                {{ in_array($column, $columnOrder) ? 'checked' : '' }}>
                            <span>{{ ucwords(str_replace('_', ' ', $column)) }}</span>
                        </li>
                    @endforeach
                </ul>
            </form>

            <div class="flex justify-end gap-3 mt-4">
                <button onclick="closeColumnOrderModal()" class="btn">Cancel</button>
                <button onclick="saveColumnOrder()" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-5 right-5 bg-green-500 text-white px-4 py-2 rounded shadow hidden">
        ✅ Column order saved!
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        function openColumnOrderModal() {
            document.getElementById('columnOrderModal').classList.remove('hidden');
        }

        function closeColumnOrderModal() {
            document.getElementById('columnOrderModal').classList.add('hidden');
        }

        const sortable = new Sortable(document.getElementById('sortableColumns'), {
            animation: 150
        });

        function saveColumnOrder() {
            const selectedColumns = [];
            document.querySelectorAll('#sortableColumns li').forEach((el, index) => {
                const column = el.dataset.column;
                const checked = el.querySelector('input[type="checkbox"]').checked;
                selectedColumns.push({
                    column_key: column,
                    order_index: index,
                    is_visible: checked ? 1 : 0
                });
            });

            fetch('{{ route("admin.save.column.order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    table_name: 'project_details',
                    columns: selectedColumns
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                closeColumnOrderModal();
                location.reload();
            });
        }


    </script>

</x-admin.wrapper>
