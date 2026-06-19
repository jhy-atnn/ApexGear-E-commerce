<!-- ── User Profile Modal ── -->
<div id="userProfileModal" class="profile-modal-container">
    <div class="profile-modal-content">
        <div class="profile-modal-header">
            <button type="button" class="btn-close-modal"><i class="fas fa-arrow-left"></i></button>
            <h5 class="profile-modal-title">Edit Profile</h5>
        </div>
        <div class="profile-modal-body">
            <div id="profileAlert" class="alert d-none"></div>
            <form id="userProfileForm" enctype="multipart/form-data">

                <!-- Avatar -->
                <div class="profile-avatar-section">
                    <div class="profile-avatar-large position-relative">
                        <?php
                            $avatarUrl = isset($_SESSION['user']['profile_picture']) && !empty($_SESSION['user']['profile_picture'])
                                         ? $_SESSION['user']['profile_picture']
                                         : 'https://via.placeholder.com/400';
                        ?>
                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="User Avatar" id="userModalAvatar">
                        <label for="userProfilePicture" class="btn btn-sm btn-primary position-absolute" title="Change photo">
                            <i class="fas fa-pen"></i>
                        </label>
                        <input type="file" id="userProfilePicture" name="profile_picture" class="d-none" accept="image/*">
                    </div>
                    <div class="text-muted small">Upload a JPG, PNG, or WEBP profile picture.</div>
                </div>

                <!-- Personal Info -->
                <div class="profile-section-label"><i class="fas fa-user" style="color:rgba(0,194,255,.5);font-size:.7rem;"></i> Personal Info</div>
                <div class="row gx-3 gy-3">
                    <div class="col-12 col-md-6 form-group">
                        <label for="userFirstName">First Name</label>
                        <input type="text" id="userFirstName" name="first_name" class="form-control" placeholder="First name" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['first_name'] ?? '') : ''; ?>" oninput="filterLettersOnly(this)">
                    </div>
                    <div class="col-12 col-md-6 form-group">
                        <label for="userLastName">Last Name</label>
                        <input type="text" id="userLastName" name="last_name" class="form-control" placeholder="Last name" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['last_name'] ?? '') : ''; ?>" oninput="filterLettersOnly(this)">
                    </div>
                </div>
                <div class="form-group">
                    <label for="userBio">Bio</label>
                    <textarea id="userBio" name="bio" class="form-control" rows="3" placeholder="Tell us a little about yourself…"><?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['bio'] ?? '') : ''; ?></textarea>
                </div>

                <!-- Contact & Details -->
                <div class="profile-section-label"><i class="fas fa-id-card" style="color:rgba(0,194,255,.5);font-size:.7rem;"></i> Contact &amp; Details</div>
                <div class="row gx-3 gy-3">
                    <div class="col-12 col-md-6 form-group">
                        <label for="userEmail">Email</label>
                        <input type="email" id="userEmail" name="email" class="form-control" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['email'] ?? '') : ''; ?>" placeholder="your@email.com">
                    </div>
                    <div class="col-12 col-md-6 form-group">
                        <label for="userContact">Contact Number</label>
                        <input
                            type="text"
                            id="userContact"
                            name="phone"
                            class="form-control"
                            inputmode="numeric"
                            maxlength="11"
                            pattern="09[0-9]{9}"
                            title="Use an 11-digit number that starts with 09"
                            placeholder="09171234567"
                            value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['phone'] ?? '') : ''; ?>"
                            oninput="filterPhoneNumber(this)">
                    </div>
                </div>
                <div class="row gx-3 gy-3">
                    <div class="col-12 col-md-6 form-group">
                        <label for="userGender">Gender</label>
                        <?php $uGender = isset($_SESSION['user']) ? $_SESSION['user']['gender'] : ''; ?>
                        <select id="userGender" name="gender" class="form-control">
                            <option value="" <?php echo $uGender == '' ? 'selected' : ''; ?>>Select</option>
                            <option value="Male" <?php echo $uGender == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $uGender == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $uGender == 'Other' ? 'selected' : ''; ?>>Other</option>
                            <option value="Prefer not to say" <?php echo $uGender == 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 form-group">
                        <label for="userBirthdate">Birthdate</label>
                        <div class="input-group">
                            <input type="date" id="userBirthdate" name="birthday" class="form-control datepicker-input" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['birthday'] ?? '') : ''; ?>" />
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="profile-section-label"><i class="fas fa-shipping-fast" style="color:rgba(0,194,255,.5);font-size:.7rem;"></i> Shipping Address</div>
                <div class="form-group">
                    <label for="userStreetAddress">Street Address</label>
                    <input type="text" id="userStreetAddress" name="street_address" class="form-control" placeholder="Street address" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['street_address'] ?? '') : ''; ?>">
                </div>
                <div class="row gx-3 gy-3">
                    <div class="col-12 col-md-6 form-group">
                        <label for="userCity">City</label>
                        <input type="text" id="userCity" name="city" class="form-control" placeholder="City" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['city'] ?? '') : ''; ?>" oninput="filterLettersOnly(this)">
                    </div>
                    <div class="col-12 col-md-6 form-group">
                        <label for="userPostalCode">Postal Code</label>
                        <input type="text" id="userPostalCode" name="postal_code" class="form-control" placeholder="Postal code" value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['postal_code'] ?? '') : ''; ?>" oninput="filterNumbersOnly(this)">
                    </div>
                </div>

            </form>
        </div>
        <div class="profile-modal-footer">
            <button type="button" class="btn btn-secondary btn-close-modal-alt">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveProfile()">
                <i class="fas fa-check" style="margin-right:6px;font-size:.75rem;"></i>Save Changes
            </button>
        </div>
    </div>
</div>

<style>
    .pp-link-wrapper {
        position: relative;
    }


    .order-status-item-question span {
        display: block;
        text-align: center;
        margin-bottom: 5px;
    }
</style>

<!-- Profile Dropdown Logic -->
<script>
    <?php if (isset($_SESSION['user'])): ?>
        (function() {
            const panel = document.createElement('div');
            panel.id = 'profilePanel';
            panel.className = 'profile-panel';
            panel.innerHTML = `
                <div class="pp-header">
                    <div class="pp-avatar"><?php echo !empty($_SESSION['user']['profile_picture']) ? '<img src="' . htmlspecialchars($_SESSION['user']['profile_picture']) . '" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">' : htmlspecialchars($_SESSION['user']['avatar']); ?></div>
                    <div>
                        <div class="pp-username"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
                        <div class="pp-email"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></div>
                    </div>
                </div>
                <div class="pp-divider"></div>
                <a href="javascript:void(0)" class="pp-link"><i class="fas fa-user"></i> My Profile</a>
                <div class="pp-link-wrapper">
                <div class="pp-divider"></div>
                <a href="logout.php" class="pp-link pp-logout"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
            `;
            document.body.appendChild(panel);

        })();

        function toggleProfilePanel(e) {
            e.stopPropagation();
            const panel = document.getElementById('profilePanel');
            const btn = document.getElementById('profileToggle');
            const rect = btn.getBoundingClientRect();
            const isOpen = panel.classList.contains('open');

            if (isOpen) {
                panel.classList.remove('open');
                return;
            }

            panel.style.top = (rect.bottom + window.scrollY + 12) + 'px';
            panel.style.left = 'auto';
            panel.style.right = (document.documentElement.clientWidth - rect.right) + 'px';
            panel.classList.add('open');
        }

        document.addEventListener('click', function(e) {
            const panel = document.getElementById('profilePanel');
            const btn = document.getElementById('profileToggle');
            if (panel && btn && !btn.contains(e.target) && !panel.contains(e.target)) {
                panel.classList.remove('open');
            }
        });

        window.addEventListener('scroll', function() {
            const panel = document.getElementById('profilePanel');
            if (panel) panel.classList.remove('open');
        });

        // User Profile Modal Logic
        const profileModal = document.getElementById('userProfileModal');
        const birthInput = document.getElementById('userBirthdate');
        if (birthInput) {
            birthInput.addEventListener('click', function() {
                if (typeof birthInput.showPicker === 'function') {
                    try {
                        birthInput.showPicker();
                    } catch (err) {}
                }
            });

            birthInput.addEventListener('keydown', function(e) {
                if (e.key !== 'Tab') e.preventDefault();
            });
        }

        const myProfileLink = document.querySelector('.pp-link[href="javascript:void(0)"]'); // Simplified selector
        const closeModalBtn = document.querySelector('#userProfileModal .btn-close-modal');
        const closeModalAltBtn = document.querySelector('#userProfileModal .btn-close-modal-alt');

        myProfileLink.addEventListener('click', (e) => {
            e.preventDefault();
            profileModal.classList.add('open');
            document.getElementById('profilePanel').classList.remove('open');
        });

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => {
                profileModal.classList.remove('open');
            });
        }

        if (closeModalAltBtn) {
            closeModalAltBtn.addEventListener('click', () => {
                profileModal.classList.remove('open');
            });
        }

        profileModal.addEventListener('click', (e) => {
            if (e.target === profileModal) {
                profileModal.classList.remove('open');
            }
        });

        document.getElementById('userProfilePicture').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const avatar = document.getElementById('userModalAvatar');
            if (file && avatar) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        function showProfileAlert(message) {
            const alertBox = document.getElementById('profileAlert');
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
        }

        function hideProfileAlert() {
            const alertBox = document.getElementById('profileAlert');
            alertBox.classList.add('d-none');
        }

        function filterLettersOnly(el) {
            const original = el.value;
            const filtered = original.replace(/[^A-Za-z\s]/g, '');
            if (original !== filtered) {
                el.value = filtered;
                showProfileAlert('Please only use letters');
            } else {
                hideProfileAlert();
            }
        }

        function filterNumbersOnly(el) {
            const original = el.value;
            const filtered = original.replace(/[^0-9]/g, '');
            if (original !== filtered) {
                el.value = filtered;
                showProfileAlert('Please only use numbers');
            } else {
                hideProfileAlert();
            }
        }

        function filterPhoneNumber(el) {
            const original = el.value;
            const filtered = original.replace(/[^0-9]/g, '').slice(0, 11);
            if (original !== filtered) {
                el.value = filtered;
                showProfileAlert('Contact number must use digits only.');
                return;
            }
            if (filtered && !filtered.startsWith('09')) {
                showProfileAlert('Contact number must start with 09.');
                return;
            }
            hideProfileAlert();
        }

        async function saveProfile() {
            const form = document.getElementById('userProfileForm');
            const phoneField = document.getElementById('userContact');
            const phonePattern = /^09\d{9}$/;

            if (phoneField && phoneField.value.trim() && !phonePattern.test(phoneField.value.trim())) {
                showProfileAlert('Contact number must start with 09 and be exactly 11 digits.');
                phoneField.focus();
                return;
            }

            const formData = new FormData(form);
            const alertBox = document.getElementById('profileAlert');

            try {
                const response = await fetch('actions/profile_action.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alertBox.className = 'alert alert-success';
                    alertBox.textContent = result.message;
                    alertBox.classList.remove('d-none');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alertBox.className = 'alert alert-danger';
                    alertBox.textContent = result.message;
                    alertBox.classList.remove('d-none');
                }
            } catch (err) {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = 'An error occurred while saving.';
                alertBox.classList.remove('d-none');
            }
        }
    <?php endif; ?>
</script>
