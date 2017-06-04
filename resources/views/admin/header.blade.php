
<header class="main-header">
    <a href="/" class="logo"><b>Gethype</a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ asset('/images/users/'.Auth::user()->photo()) }}" class="user-image" alt="User Image"/>
                        <span class="hidden-xs">{{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <img src="{{ asset('/images/users/'.Auth::user()->photo()) }}" class="img-circle" alt="User Image" />
                            <p>
                                {{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}
                                <small>Member since Nov. 2012</small>
                            </p>
                        </li>

                        <li class="user-footer">
                            <div class="pull-right">
                                <a href="{{ url('/logout') }}" class="btn btn-default btn-flat" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a>
                                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>