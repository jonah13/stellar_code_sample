<ul>
    <li class="first_level @if (strpos(Route::currentRouteName(), 'admin.index') === 0) {{'section_active'}} @endif">
        <a href="{{ URL::route('index') }}">
            <span class="icon_house_alt first_level_icon"></span>
            <span class="menu-title">Dashboard</span>
        </a>
    </li>
    @if ($user->isSysAdmin())
        <li class="first_level @if (strpos(Route::currentRouteName(), 'admin.users') === 0) {{'section_active'}} @endif">
            <a href="{{ URL::route('admin.users.index') }}">
                <span class="el-icon-adult first_level_icon"></span>
                <span class="menu-title">Admins</span>
            </a>
        </li>
    @endif
    <li class="first_level @if (strpos(Route::currentRouteName(), 'admin.patients') === 0) {{'section_active'}} @endif">
        <a href="{{ URL::route('admin.patients.index') }}">
            <span class="social_myspace first_level_icon"></span>
            <span class="menu-title">Patients</span>
        </a>
    </li>
    @if ($user->isSysAdmin())
        <li class="first_level @if (strpos(Route::currentRouteName(), 'admin.insurance_companies') === 0) {{'section_active'}} @endif">
            <a href="{{ URL::route('admin.insurance_companies.index') }}">
                <span class="icon_document_alt first_level_icon"></span>
                <span class="menu-title">Insurance Companies</span>
            </a>
        </li>
    @endif
    <li class="first_level @if ((strpos(Route::currentRouteName(), 'admin.regions') === 0) || (strpos(Route::currentRouteName(), 'admin.programs') === 0)) {{'section_active'}} @endif">
        <a href="{{ URL::route('admin.regions.index') }}">
            <span class="icon_document_alt first_level_icon"></span>
            <span class="menu-title">Regions</span>
        </a>
    </li>
    @if ($user->isSysAdmin())
        <li class="first_level">
            <a href="javascript:void(0)">
                <span class="el-icon-wrench first_level_icon"></span>
                <span class="menu-title">General Settings</span>
            </a>
            <ul>
                <li><a href="{{ URL::route('admin.phones.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.phones') === 0) {{'act_nav'}} @endif">
                        Phone Pool</a>
                </li>
                <li><a href="{{ URL::route('admin.discontinue_tracking_reasons.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.discontinue_tracking_reasons') === 0) {{'act_nav'}} @endif">
                        Discontinue Tracking Reasons</a>
                </li>
                <li><a href="{{ URL::route('admin.outreach_codes.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.outreach_codes') === 0) {{'act_nav'}} @endif">
                        Outreach Codes</a>
                </li>
            </ul>
        </li>
        <li class="first_level">
            <a href="javascript:void(0)">
                <span class="icon_document_alt first_level_icon"></span>
                <span class="menu-title">Reports</span>
            </a>
            <ul>
                <li><a href="{{ URL::route('admin.programs.report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.programs.report') === 0) {{'act_nav'}} @endif">Program
                        Reports</a></li>
                <li><a href="{{ URL::route('admin.reports.scheduled_visit_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.scheduled_visit_report') === 0) {{'act_nav'}} @endif">Scheduled
                        Visit Report</a></li>
                <li><a href="{{ URL::route('admin.reports.incentive_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.incentive_report') === 0) {{'act_nav'}} @endif">Incentive
                        Report</a></li>
                <li><a href="{{ URL::route('admin.reports.pregnancy_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.pregnancy_report') === 0) {{'act_nav'}} @endif">Pregnancy
                        Reports</a></li>
                <li><a href="{{ URL::route('admin.reports.returned_gift_card_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.returned_gift_card_report') === 0) {{'act_nav'}} @endif">Returned
                        Gift Card Report</a></li>
                <li><a href="{{ URL::route('admin.reports.outreach_codes_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.outreach_codes_report') === 0) {{'act_nav'}} @endif">Outreach
                        Code Report</a></li>
            </ul>
        </li>
    @endif
</ul>