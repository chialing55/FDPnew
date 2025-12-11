<div class="grid grid-cols-3 gap-4">

    @php
        // role code 對應顯示文字
        $roleLabels = [
            'plot_manager' => '樣區負責人',
            'team_partner' => '合作單位',
        ];
    @endphp

    @foreach ($teams as $card)
        @php
            /** @var \App\Models\Web\Team $team */
            $team = $card->team;
            $roles = $card->roles; // Collection of role code
            $sites = $card->sites; // Collection of Site model
        @endphp

        {{-- 整張卡片變成可點連結 --}}
        <a href="{{ $team->website_url ?: '#' }}" @if ($team->website_url) target="_blank" rel="noopener" @endif
            class="group block rounded-xl border border-gray-200 bg-white p-4 !font-normal !no-underline shadow-sm transition hover:-translate-y-0.5 hover:!font-normal hover:!no-underline hover:shadow-lg">

            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                {{-- 左側：logo --}}
                <div class="flex-shrink-0">
                    @if ($team->logo_path)
                        <img src="{{ asset('storage/' . $team->logo_path) }}" alt="{{ $team->name }}"
                            class="h-20 w-20 rounded-full object-cover ring-2 ring-forest/10">
                    @else
                        {{-- 沒有 logo 時的預設圈圈 --}}
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-forest-mist text-sm text-forest">
                            {{ Str::limit($team->institution ?? $team->name, 4, '') }}
                        </div>
                    @endif
                </div>

                {{-- 右側：文字區塊 --}}
                <div class="flex-1 space-y-1">
                    {{-- 標題 + 標籤列 --}}
                    <div class="flex flex-wrap items-center gap-1">
                        {{-- 主標題 --}}
                        <h3 class="mb-0 pt-0 text-base font-semibold text-gray-900">
                            @if ($team->team_type == 'academic')
                                {{ $team->pi_name }}
                            @else
                                {{ $team->institution }}{{ $team->department ? ' ' . $team->department : '' }}
                            @endif
                        </h3>
                        <div>
                            {{-- 角色標籤 --}}
                            @if ($showRoleLabel)
                                @foreach ($roles as $role)
                                    <span
                                        class="inline-flex items-center rounded-full bg-forest/10 px-2.5 py-0.5 text-xs font-medium text-forest">
                                        {{ $roleLabels[$role] ?? $role }}
                                    </span>
                                @endforeach
                            @endif

                            {{-- 樣區標籤（可能多個） --}}
                            @if ($showSiteLabel)
                                @foreach ($sites as $site)
                                    <span
                                        class="inline-flex items-center rounded-full bg-forest-mist px-2.5 py-0.5 text-xs font-medium text-forest-canopy">
                                        {{ $site->name_zh_tw ?? ($site->name_en ?? ($site->name ?? '')) }}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- 單位 / 系所行 --}}
                    <p class="text-sm text-gray-700">
                        @if ($team->team_type == 'academic')
                            {{ $team->institution }}{{ $team->department ? '・' . $team->department : '' }}
                        @else
                        @endif
                    </p>
                </div>
            </div>
        </a>
    @endforeach


</div>
