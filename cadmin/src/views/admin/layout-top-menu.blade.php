<nav class="navbar navbar-static-top" role="navigation">
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
    </a>
    <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
            <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <span class="hidden-xs"><i class="fa fa-user"></i> {{ Auth::user()->display_name }}</span>
                </a>
                <ul class="dropdown-menu">
                    <li class="user-header" style="height:auto;">
                        <p>
                          {{ Auth::user()->display_name }}
                          <small>@if (Auth::user()->isRoot) root @else {{Auth::user()->privilege->label}} @endif</small>
                        </p>
                    </li>
                    <li class="user-footer">
                        <div class="pull-left">
                            <a href="{{ admin::url('profile') }}" class="btn btn-default btn-flat">Profile</a>
                        </div>
                        <div class="pull-right">
                            <a href="{{ admin::url('logout') }}" class="btn btn-default btn-flat">Sign out</a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>