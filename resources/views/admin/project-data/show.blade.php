<x-admin.wrapper>
    <x-slot name="title">
        {{ __('Project Detail') }}
    </x-slot>

    <div class="py-6 space-y-4">
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Basic Info</h2>
            <p><strong>Project Code:</strong> {{ $project->project_code }}</p>
            <p><strong>Project Name:</strong> {{ $project->project_name }}</p>
            <p><strong>Developer Name:</strong> {{ $project->developer_name }}</p>
            <p><strong>License No:</strong> {{ $project->license_number }}</p>
            <p><strong>License Valid From:</strong> {{ $project->license_valid_from }}</p>
            <p><strong>License Valid To:</strong> {{ $project->license_valid_to }}</p>
            <p><strong>Permit No:</strong> {{ $project->permit_number }}</p>
            <p><strong>Permit Valid From:</strong> {{ $project->permit_valid_from }}</p>
            <p><strong>Permit Valid To:</strong> {{ $project->permit_valid_to }}</p>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Contact Info</h2>
            <p><strong>Email:</strong> {{ $project->email }}</p>
            <p><strong>Phone:</strong> {{ $project->phone }}</p>
            <p><strong>Website:</strong> {{ $project->website }}</p>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Location</h2>
            <p><strong>District:</strong> {{ $project->district }}</p>
            <p><strong>State:</strong> {{ $project->state }}</p>
            <p><strong>Registered Address:</strong> {{ $project->registered_address }}</p>
            <p><strong>Business Address:</strong> {{ $project->business_address }}</p>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-semibold mb-4">Other Details</h2>
            <p><strong>Agreement Type:</strong> {{ $project->agreement_type }}</p>
            <p><strong>First VP Date:</strong> {{ $project->first_vp_date }}</p>
            <p><strong>New Construction Period:</strong> {{ $project->new_construction_period }}</p>
            <p><strong>Development Info:</strong> {{ $project->development_info }}</p>
            <p><strong>Overall Status:</strong> {{ $project->overall_status }}</p>
        </div>

        <div class="bg-white p-6 rounded shadow mt-6">
            <h2 class="text-lg font-semibold mb-4">Unit Summaries</h2>
            <table class="table w-full">
                <thead>
                    <tr class="bg-base-200 text-left">
                        <th>House Type</th>
                        <th>Floors</th>
                        <th>Rooms</th>
                        <th>Toilets</th>
                        <th>Built-up Area</th>
                        <th>Unit Count</th>
                        <th>Min Price</th>
                        <th>Max Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($project->unitSummaries as $unit)
                        <tr>
                            <td>{{ $unit->house_type }}</td>
                            <td>{{ $unit->floors }}</td>
                            <td>{{ $unit->rooms }}</td>
                            <td>{{ $unit->toilets }}</td>
                            <td>{{ $unit->built_up_area }}</td>
                            <td>{{ $unit->unit_count }}</td>
                            <td>{{ $unit->min_price }}</td>
                            <td>{{ $unit->max_price }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No unit summary data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @php
            // Preprocess summary price ranges with unique colors
            $priceGroups = collect($project->unitSummaries)->map(function ($summary, $index) {
                return [
                    'house_type' => $summary->house_type,
                    'min' => (float) str_replace(',', '', $summary->min_price),
                    'max' => (float) str_replace(',', '', $summary->max_price),
                    'color' => ['bg-yellow-100', 'bg-green-100', 'bg-blue-100', 'bg-red-100', 'bg-purple-100'][$index % 5], // rotate colors
                ];
            });
        @endphp

        <div class="bg-white p-6 rounded shadow mt-6">
            <h2 class="text-lg font-semibold mb-4">Unit Box Details</h2>
            <table class="table w-full">
                <thead>
                    <tr class="bg-base-200 text-left">
                        <th>No. Unit</th>
                        <th>Lot / Plot</th>
                        <th>Kuota Bumi</th>
                        <th>Harga Jualan</th>
                        <th>Harga SPJB</th>
                        <th>Status Jualan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($project->unitBoxes as $box)
                        @php
                            // Clean the price
                            $price = (float) str_replace(['RM', ',', ' '], '', $box->harga_jualan);
                            $colorClass = '';

                            foreach ($priceGroups as $group) {
                                if ($price >= $group['min'] && $price <= $group['max']) {
                                    $colorClass = $group['color'];
                                    break;
                                }
                            }
                        @endphp
                        <tr class="{{ $colorClass }}">
                            <td>{{ $box->no_unit }}</td>
                            <td>{{ $box->no_pt_lot_plot }}</td>
                            <td>{{ $box->kuota_bumi }}</td>
                            <td>{{ $box->harga_jualan }}</td>
                            <td>{{ $box->harga_spjb }}</td>
                            <td>{{ $box->status_jualan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No unit box data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('admin.view.project.data') }}" class="text-blue-500 underline">← Back to list</a>
    </div>
    
</x-admin.wrapper>
