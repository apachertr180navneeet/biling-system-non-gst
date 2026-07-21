<nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
	id="layout-navbar">
	<div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none d-block">
		<a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
			<i class="bx bx-menu bx-sm"></i>
		</a>
	</div>

	<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
		<!-- Search -->
		<div class="navbar-nav align-items-center">
			<div class="nav-item d-flex align-items-center text-primary">
				<i class="bx bx-calendar fs-4 lh-0 d-none d-sm-inline"></i>&nbsp;
				<span class="d-none d-sm-inline">{{ date('D') }} {{ date('d M Y') }}</span>
				<span class="d-sm-none">{{ date('d M Y') }}</span>
			</div>
		</div>
		<!-- /Search -->

		<ul class="navbar-nav flex-row align-items-center ms-auto">
			<!-- Theme Toggle -->
			<li class="nav-item me-3">
				<button class="btn btn-icon btn-link p-0" id="theme-toggle-btn" title="Toggle Theme" style="box-shadow: none !important; border: none; background: transparent; cursor: pointer;">
					<i class="bx bx-moon fs-4" id="theme-toggle-icon" style="transition: transform 0.3s ease;"></i>
				</button>
			</li>
			<!--/ Theme Toggle -->
			<!-- User -->
			<li class="nav-item navbar-dropdown dropdown-user dropdown">
				<a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
					data-bs-toggle="dropdown">
					<div class="avatar avatar-online">
						<img src="{{asset('assets/admin/img/avatars/1.png')}}" alt="admin" class="w-px-40 h-auto rounded-circle" />
					</div>
				</a>
				<ul class="dropdown-menu dropdown-menu-end">
					<li>
						<a class="dropdown-item" href="{{route('admin.profile')}}">
							<div class="d-flex">
								<div class="flex-shrink-0 me-3">
									<div class="avatar avatar-online">
										@if(!empty(Auth::user()->avatar) && file_exists(public_path('/').Auth::user()->avatar))
		                                    <img src="{{asset(Auth::user()->avatar)}}" alt="User Image" class="w-px-40 h-auto rounded-circle">
		                                @else
		                                    <img src="{{asset('assets/admin/img/avatars/1.png')}}"  alt="User Image" class="w-px-40 h-auto rounded-circle">
		                                @endif
									</div>
								</div>
								<div class="flex-grow-1">
									<span class="fw-medium d-block">{{Auth::user()->full_name}}</span>
									<small class="text-muted">{{ucfirst(Auth::user()->role)}}</small>
								</div>
							</div>
						</a>
					</li>
					<li>
						<div class="dropdown-divider"></div>
					</li>
					<li>
						<a class="dropdown-item" href="{{route('admin.profile')}}">
							<i class="bx bx-user me-2"></i>
							<span class="align-middle">My Profile</span>
						</a>
					</li>
					<li>
						<a class="dropdown-item" href="{{route('admin.change.password')}}">
							<i class="bx bx-key me-2"></i>
							<span class="align-middle">Change Password</span>
						</a>
					</li>
					<li>
						<div class="dropdown-divider"></div>
					</li>
					<li>
						<a class="dropdown-item" href="{{route('admin.logout')}}">
							<i class="bx bx-power-off me-2"></i>
							<span class="align-middle">Log Out</span>
						</a>
					</li>
				</ul>
			</li>
			<!--/ User -->
		</ul>
	</div>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const themeBtn = document.getElementById('theme-toggle-btn');
		const themeIcon = document.getElementById('theme-toggle-icon');
		
		function updateIcon(theme) {
			if (theme === 'dark') {
				themeIcon.className = 'bx bx-sun fs-4 text-warning';
				themeIcon.style.transform = 'rotate(180deg)';
			} else {
				themeIcon.className = 'bx bx-moon fs-4 text-secondary';
				themeIcon.style.transform = 'rotate(0deg)';
			}
		}
		
		// Initial state
		const currentTheme = localStorage.getItem('admin-theme') || 'light';
		updateIcon(currentTheme);
		
		themeBtn.addEventListener('click', function() {
			const theme = localStorage.getItem('admin-theme') || 'light';
			const newTheme = theme === 'light' ? 'dark' : 'light';
			
			localStorage.setItem('admin-theme', newTheme);
			document.documentElement.setAttribute('data-theme', newTheme);
			
			if (newTheme === 'dark') {
				document.documentElement.classList.add('dark-style');
				document.documentElement.classList.remove('light-style');
			} else {
				document.documentElement.classList.add('light-style');
				document.documentElement.classList.remove('dark-style');
			}
			
			updateIcon(newTheme);
		});
	});
	</script>
</nav>