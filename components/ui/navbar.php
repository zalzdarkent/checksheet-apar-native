<nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
    <div class="container-fluid">
        <!-- Live Clock Display -->
        <div class="d-none d-lg-flex flex-column justify-content-center me-4 mt-1">
            <div class="text-muted fw-bold mb-1" style="font-size: 0.85rem;">
                <i class="fas fa-calendar-alt me-1"></i><span id="liveDateDisplay"></span>
            </div>
            <div class="text-primary fw-bolder" style="font-size: 1.6rem; line-height: 1; margin-top: -3px;">
                <i class="far fa-clock me-1" style="font-size: 1.2rem;"></i><span id="liveTimeDisplay"></span>
            </div>
        </div>

        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
            <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                    aria-expanded="false" aria-haspopup="true">
                    <i class="fa fa-search"></i>
                </a>
                <ul class="dropdown-menu dropdown-search animated fadeIn">
                    <form class="navbar-left navbar-form nav-search">
                        <div class="input-group">
                            <input type="text" placeholder="Search ..." class="form-control" />
                        </div>
                    </form>
                </ul>
            </li>
            <!-- <li class="nav-item topbar-icon dropdown hidden-caret">
                <ul class="dropdown-menu messages-notif-box animated fadeIn" aria-labelledby="messageDropdown">
                    <li>
                        <div class="dropdown-title d-flex justify-content-between align-items-center">
                            Messages
                            <a href="#" class="small">Mark all as read</a>
                        </div>
                    </li>
                    <li>
                        <div class="message-notif-scroll scrollbar-outer">
                            <div class="notif-center">
                                <a href="#">
                                    <div class="notif-img">
                                        <img src="assets/img/jm_denis.jpg" alt="Img Profile" />
                                    </div>
                                    <div class="notif-content">
                                        <span class="subject">Jimmy Denis</span>
                                        <span class="block"> How are you ? </span>
                                        <span class="time">5 minutes ago</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <a class="see-all" href="javascript:void(0);">See all messages<i class="fa fa-angle-right"></i>
                        </a>
                    </li>
                </ul>
            </li> -->
            <li class="nav-item">
                <button class="btn btn-info btn-sm btn-round px-3 me-2 d-none d-md-flex align-items-center"
                    data-bs-toggle="modal" data-bs-target="#globalMapViewModal">
                    <i class="fas fa-map-marked-alt me-2"></i> Lihat Map
                </button>
            </li>
            <!-- <li class="nav-item topbar-icon dropdown hidden-caret">
                <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-bell"></i>
                    <span class="notification">1</span>
                </a>
                <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                    <li>
                        <div class="dropdown-title">
                            You have 4 new notification
                        </div>
                    </li>
                    <li>
                        <div class="notif-scroll scrollbar-outer">
                            <div class="notif-center">
                                <a href="#">
                                    <div class="notif-icon notif-primary">
                                        <i class="fa fa-user-plus"></i>
                                    </div>
                                    <div class="notif-content">
                                        <span class="block"> New user registered </span>
                                        <span class="time">5 minutes ago</span>
                                    </div>
                                </a>
                                <a href="#">
                                    <div class="notif-icon notif-success">
                                        <i class="fa fa-comment"></i>
                                    </div>
                                    <div class="notif-content">
                                        <span class="block">
                                            Rahmad commented on Admin
                                        </span>
                                        <span class="time">12 minutes ago</span>
                                    </div>
                                </a>
                                <a href="#">
                                    <div class="notif-img">
                                        <img src="assets/img/profile2.jpg" alt="Img Profile" />
                                    </div>
                                    <div class="notif-content">
                                        <span class="block">
                                            Reza send messages to you
                                        </span>
                                        <span class="time">12 minutes ago</span>
                                    </div>
                                </a>
                                <a href="#">
                                    <div class="notif-icon notif-danger">
                                        <i class="fa fa-heart"></i>
                                    </div>
                                    <div class="notif-content">
                                        <span class="block"> Farrah liked Admin </span>
                                        <span class="time">17 minutes ago</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <a class="see-all" href="javascript:void(0);">See all notifications<i
                                class="fa fa-angle-right"></i>
                        </a>
                    </li>
                </ul>
            </li> -->

            <li class="nav-item topbar-user dropdown hidden-caret">
                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <div class="avatar-sm">
                        <img src="storage/users/<?= $_SESSION['user_photo'] ?: 'default.png' ?>" alt="..." class="avatar-img rounded-circle" />
                    </div>
                    <span class="profile-username">
                        <span class="op-7">Hi,</span>
                        <span class="fw-bold"><?= $_SESSION['user_name'] ?></span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                        <li>
                            <div class="user-box">
                                <div class="avatar-sm">
                                    <img src="storage/users/<?= $_SESSION['user_photo'] ?: 'default.png' ?>"
                                        alt="image profile" class="avatar-img rounded-circle" />
                                </div>
                                <div class="u-text">
                                    <h4><?= $_SESSION['user_name'] ?></h4>
                                    <p class="text-muted"><?= $_SESSION['user_npk'] ?></p>
                                    <a href="?page=edit-user&id=<?= $_SESSION['user_id'] ?>"
                                        class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="javascript:void(0)" id="logoutBtn">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </div>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<script>
    function updateClockDisplay() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        const dayName = days[now.getDay()];
        const day = String(now.getDate()).padStart(2, '0');
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const dateElem = document.getElementById('liveDateDisplay');
        const timeElem = document.getElementById('liveTimeDisplay');
        
        if(dateElem) {
            dateElem.textContent = `${dayName}, ${day} ${monthName} ${year}`;
        }
        if(timeElem) {
            timeElem.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }
    
    updateClockDisplay();
    setInterval(updateClockDisplay, 1000);
</script>
