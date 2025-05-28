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

        @php
            // Check if First VP Date is valid
            $firstVPDate = null;
            if (!empty($project->first_vp_date) && strtotime($project->first_vp_date)) {
                $firstVPDate = \Carbon\Carbon::parse($project->first_vp_date);
            }

            // Extract number of months from "22 Bulan"
            preg_match('/\d+/', $project->extension_approved ?? '', $matches);
            $extensionMonths = isset($matches[0]) ? (int) $matches[0] : 0;

            // Calculate new VP date
            $calculatedNewVPDate = $firstVPDate
                ? $firstVPDate->copy()->addMonths($extensionMonths)->format('d M Y')
                : 'N/A';
        @endphp


        <div class="bg-white p-6 rounded shadow overflow-x-auto">
            <h2 class="text-lg font-semibold mb-4">Other Details</h2>
            <p><strong>Agreement Type:</strong> {{ $project->agreement_type }}</p>
            <p><strong>Original Construction Period:</strong> {{ $project->original_construction_period }}</p>
            <p><strong>First SPA Date:</strong> {{ $project->first_pjb_date }}</p>
            <p><strong>First Plan VP Date:</strong> 
                {{ $firstVPDate ? $firstVPDate->format('d M Y') : 'N/A' }}
            </p>
            <p><strong>VP Amendment:</strong> {{ $project->vp_amendment }}</p>
            <p><strong>Extension Approved:</strong> {{ $project->extension_approved }}</p>
            <p><strong>New Construction Period:</strong> {{ $project->new_construction_period }}</p>
            <p><strong>New Plan Vp Date:</strong> {{ $calculatedNewVPDate }}</p>
        </div>


        <div class="bg-white p-6 rounded shadow mt-6">
            <h2 class="text-lg font-semibold mb-4">Unit Summaries</h2>
            
            <div class="overflow-x-auto">
                <table class="table w-full min-w-[1000px]">
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
                            <th>Actual %</th>
                            <th>Component Status</th>
                            <th>CCC Date</th>
                            <th>VP Date</th>
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
                                <td>{{ $unit->actual_percentage }}</td>
                                <td>{{ $unit->component_status }}</td>
                                <td>{{ $unit->ccc_date }}</td>
                                <td>{{ $unit->vp_date }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">No unit summary data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <div class="bg-white p-6 rounded shadow mt-6">
            <h2 class="text-lg font-semibold mb-4">Unit Box Details</h2>
            <form method="GET" class="flex flex-wrap gap-4 items-center mb-4">
                <input type="text" name="no_unit" placeholder="No Unit/PT/Lot/Plot" class="input input-bordered" value="{{ request('no_unit') }}">

                <select name="kuota_bumi" class="input input-bordered">
                    <option value="">Sila Pilih</option>
                    @foreach ($kuotaBumiOptions as $value)
                        <option value="{{ $value }}" {{ request('kuota_bumi') == $value ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>

                <select name="status_jualan" class="input input-bordered">
                    <option value="">Sila Pilih</option>
                    @foreach ($statusJualanOptions as $value)
                        <option value="{{ $value }}" {{ request('status_jualan') == $value ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">Cari</button>
            </form>

            <div class="mb-4">
                <span class="badge">Jumlah Unit: {{ $totalUnits }}</span>
                <span class="badge bg-green-200">Belum Dijual: {{ $unsoldUnits }}</span>
                <span class="badge bg-yellow-200">Telah Dijual: {{ $soldUnits }}</span>
            </div>

            <table class="table w-full">
                <thead>
                    <tr>
                        <th>No. Unit</th>
                        <th>Lot / Plot</th>
                        <th>Kuota Bumi</th>
                        <th>Harga Jualan</th>
                        <th>Harga SPJB</th>
                        <th>
                <a href="{{ request()->fullUrlWithQuery(['sort_box_by' => 'status_jualan', 'sort_box_order' => (request('sort_box_by') == 'status_jualan' && request('sort_box_order') == 'asc') ? 'desc' : 'asc']) }}">
                    Status Jualan
                    @if(request('sort_box_by') == 'status_jualan')
                        {!! request('sort_box_order') == 'asc' ? '&uarr;' : '&darr;' !!}
                    @endif
                </a>
            </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($filteredBoxes as $box)
                        @php
                            $status = strtolower(trim($box->status_jualan));
                            $colorClass = $status === 'belum dijual' ? 'bg-green-100' : '';
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
                        <tr><td colspan="6">No filtered unit box data found.</td></tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        <a href="{{ route('admin.view.project.data') }}" class="text-blue-500 underline">← Back to list</a>
    </div>
    
</x-admin.wrapper>
