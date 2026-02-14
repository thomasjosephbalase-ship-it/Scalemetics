// ===== index.js =====
// COMPLETE WEBSITE - ALL FEATURES: DARK MODE, PASSWORD STRENGTH, STATS, ACTIVITY, SOCIAL LOGIN
// FIXED: Forgot password modal no longer causes layout shift

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
  
  // === SCREEN NAVIGATION ===
  const screens = {
    landing: document.getElementById('screenLanding'),
    auth: document.getElementById('screenAuth'),
    dashboard: document.getElementById('screenDashboard')
  };

  const btnBack = document.getElementById('btnBack');

  // Check if all screens exist
  if (!screens.landing || !screens.auth || !screens.dashboard) {
    console.error('Screens not found!');
    return;
  }

  function showScreen(name) {
    // Hide all screens
    Object.values(screens).forEach(s => {
      if (s) s.classList.add('hidden');
    });
    
    // Show selected screen
    if (screens[name]) {
      screens[name].classList.remove('hidden');
    }
    
    // Show back button only on Auth screen
    if (btnBack) {
      btnBack.style.visibility = (name === 'auth') ? 'visible' : 'hidden';
    }
  }

  // Start button
  const btnStart = document.getElementById('btnStart');
  if (btnStart) {
    btnStart.addEventListener('click', () => showScreen('auth'));
  }

  // Back button
  if (btnBack) {
    btnBack.addEventListener('click', () => showScreen('landing'));
  }

  // === DARK MODE TOGGLE ===
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = themeToggle?.querySelector('i');
  
  // Check for saved theme preference
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    if (themeIcon) {
      themeIcon.classList.remove('fa-moon');
      themeIcon.classList.add('fa-sun');
    }
  }
  
  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      
      if (currentTheme === 'dark') {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
        if (themeIcon) {
          themeIcon.classList.remove('fa-sun');
          themeIcon.classList.add('fa-moon');
        }
      } else {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        if (themeIcon) {
          themeIcon.classList.remove('fa-moon');
          themeIcon.classList.add('fa-sun');
        }
      }
    });
  }

  // === TOGGLE BETWEEN SIGNUP AND LOGIN ===
  const tabSignup = document.getElementById('tabSignup');
  const tabLogin = document.getElementById('tabLogin');
  const formSignup = document.getElementById('formSignup');
  const formLogin = document.getElementById('formLogin');
  const authTitle = document.getElementById('authTitle');
  const loginErrorMsg = document.getElementById('loginErrorMsg');

  function setAuthMode(mode) {
    if (mode === 'login') {
      // Activate login tab
      if (tabLogin) tabLogin.classList.add('active');
      if (tabSignup) tabSignup.classList.remove('active');
      // Show login form
      if (formLogin) formLogin.classList.remove('hidden');
      if (formSignup) formSignup.classList.add('hidden');
      // Change title
      if (authTitle) authTitle.textContent = 'Welcome back';
      // Hide error
      if (loginErrorMsg) loginErrorMsg.style.display = 'none';
    } else {
      // Activate signup tab
      if (tabSignup) tabSignup.classList.add('active');
      if (tabLogin) tabLogin.classList.remove('active');
      // Show signup form
      if (formSignup) formSignup.classList.remove('hidden');
      if (formLogin) formLogin.classList.add('hidden');
      // Change title
      if (authTitle) authTitle.textContent = 'Create an account';
    }
  }

  // Toggle click listeners
  if (tabSignup) tabSignup.addEventListener('click', () => setAuthMode('signup'));
  if (tabLogin) tabLogin.addEventListener('click', () => setAuthMode('login'));

  // === PASSWORD EYE BUTTONS ===
  function setupEye(btnId, inputId) {
    const btn = document.getElementById(btnId);
    const input = document.getElementById(inputId);
    if (btn && input) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        // Toggle password visibility
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        // Toggle icon
        const icon = this.querySelector('i');
        if (icon) {
          icon.classList.toggle('fa-eye');
          icon.classList.toggle('fa-eye-slash');
        }
      });
    }
  }

  // Initialize eye buttons
  setupEye('toggleSignupPass', 'signupPassword');
  setupEye('toggleLoginPass', 'loginPass');

  // === PASSWORD VALIDATION & STRENGTH METER ===
  const pwdInput = document.getElementById('signupPassword');
  const reqLen = document.getElementById('reqLen');
  const reqUp = document.getElementById('reqUp');
  const reqNum = document.getElementById('reqNum');
  const reqSpec = document.getElementById('reqSpec');
  
  // Strength meter elements
  const strengthText = document.getElementById('strengthText');
  const bars = {
    bar1: document.getElementById('bar1'),
    bar2: document.getElementById('bar2'),
    bar3: document.getElementById('bar3'),
    bar4: document.getElementById('bar4')
  };

  function updatePasswordStrength(val) {
    let strength = 0;
    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(val)) strength++;
    
    const colors = ['#b34141', '#e68a2e', '#e6c22e', '#2c7a47'];
    const texts = ['Weak', 'Fair', 'Good', 'Strong'];
    
    // Reset bars
    Object.keys(bars).forEach((bar, index) => {
      if (bars[bar]) {
        if (index < strength) {
          bars[bar].style.background = colors[strength-1];
          bars[bar].style.opacity = '1';
        } else {
          bars[bar].style.background = '#ddd';
          bars[bar].style.opacity = '0.3';
        }
      }
    });
    
    if (strengthText) {
      strengthText.textContent = texts[strength-1] || 'Very weak';
      strengthText.style.color = colors[strength-1] || '#b34141';
    }
  }

  if (pwdInput && reqLen && reqUp && reqNum && reqSpec) {
    pwdInput.addEventListener('input', function() {
      const val = this.value;

      // Helper function to update requirement UI
      const updateReq = (element, isValid, text) => {
        if (element) {
          if (isValid) {
            element.innerHTML = `<i class="fa-regular fa-circle-check"></i> ${text}`;
            element.style.color = '#1f5420';
          } else {
            element.innerHTML = `<i class="fa-regular fa-circle-xmark"></i> ${text}`;
            element.style.color = '#1e3a1a';
          }
        }
      };

      // Get original text content
      const lenText = 'At least 8 characters';
      const upText = 'At least one uppercase letter';
      const numText = 'At least one number';
      const specText = 'At least one special character';

      // Update each requirement
      updateReq(reqLen, val.length >= 8, lenText);
      updateReq(reqUp, /[A-Z]/.test(val), upText);
      updateReq(reqNum, /[0-9]/.test(val), numText);
      updateReq(reqSpec, /[!@#$%^&*(),.?":{}|<>]/.test(val), specText);
      
      // Update strength meter
      updatePasswordStrength(val);
    });
  }

  // === REGISTRATION HANDLER ===
  if (formSignup) {
    formSignup.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const username = this.querySelector('input[placeholder="Username"]').value;
      const email = this.querySelector('input[placeholder="Email address"]').value;
      const password = document.getElementById('signupPassword').value;
      
      // Disable button
      const submitBtn = this.querySelector('.btn-submit');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Creating account...';
      
      // Send to PHP backend
      fetch('php/register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          username: username,
          email: email,
          password: password
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Registration successful - auto login
          showScreen('dashboard');
          
          // Update dashboard with username
          const welcomeCard = document.querySelector('.welcome-card-small h2');
          if (welcomeCard) {
            welcomeCard.textContent = `Welcome, ${data.username}! 🧮`;
          }
        } else {
          alert('❌ ' + data.message);
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Registration failed. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      });
    });
  }

  // === LOGIN HANDLER ===
  if (formLogin) {
    formLogin.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const username = document.getElementById('loginUser').value;
      const password = document.getElementById('loginPass').value;
      const remember = document.getElementById('rememberMe') ? document.getElementById('rememberMe').checked : false;
      
      // Disable button
      const submitBtn = this.querySelector('.btn-submit');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Logging in...';
      
      // Hide previous error
      const loginErrorMsg = document.getElementById('loginErrorMsg');
      if (loginErrorMsg) loginErrorMsg.style.display = 'none';
      
      fetch('php/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          username: username,
          password: password,
          remember: remember
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Login successful
          showScreen('dashboard');
          
          // Update dashboard with username
          const welcomeCard = document.querySelector('.welcome-card-small h2');
          if (welcomeCard) {
            welcomeCard.textContent = `Welcome back, ${data.username}! 🧮`;
          }
        } else {
          // Show error
          if (loginErrorMsg) {
            loginErrorMsg.style.display = 'flex';
            loginErrorMsg.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${data.message}`;
          }
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Login failed. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      });
    });
  }

  // === FORGOT PASSWORD - FIXED MODAL UI (NO LAYOUT SHIFT) ===
  function setupForgotPassword() {
    const forgotLink = document.getElementById('forgotPasswordLink');
    const modal = document.getElementById('forgotPasswordModal');
    const closeModal = document.getElementById('closeForgotModal');
    const cancelBtn = document.getElementById('cancelForgotBtn');
    const sendBtn = document.getElementById('sendResetLinkBtn');
    const resetEmail = document.getElementById('resetEmail');
    const resetLinkContainer = document.getElementById('resetLinkContainer');
    const resetLink = document.getElementById('resetLink');
    const resetLinkClose = document.getElementById('resetLinkCloseBtn');

    // IMPORTANT: Check if modal exists
    if (!modal) {
      console.error('Forgot Password Modal not found! Check your HTML.');
      return;
    }

    // Function to open modal
    function openForgotModal(e) {
      if (e) e.preventDefault();
      if (e) e.stopPropagation();
      console.log('Opening forgot password modal');
      
      // Remove any existing overflow styles
      document.body.style.overflow = '';
      
      // Show modal
      modal.classList.remove('hidden');
      
      // Focus email input
      if (resetEmail) {
        resetEmail.value = '';
        resetEmail.classList.remove('error');
        setTimeout(() => resetEmail.focus(), 100);
      }
    }

    // Function to close modal
    function closeModalFunc(e) {
      if (e) e.preventDefault();
      if (modal) {
        modal.classList.add('hidden');
      }
      // Reset body overflow
      document.body.style.overflow = '';
    }

    // Add click handler to forgot link
    if (forgotLink) {
      // Remove all previous listeners
      const newForgotLink = forgotLink.cloneNode(true);
      forgotLink.parentNode.replaceChild(newForgotLink, forgotLink);
      
      // Add new click handler
      newForgotLink.addEventListener('click', openForgotModal);
      newForgotLink.setAttribute('id', 'forgotPasswordLink');
    }

    // Close modal handlers
    if (closeModal) {
      closeModal.addEventListener('click', closeModalFunc);
    }
    
    if (cancelBtn) {
      cancelBtn.addEventListener('click', closeModalFunc);
    }

    // Click outside to close
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModalFunc();
        }
      });
    }

    // ESC key to close
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
        closeModalFunc();
      }
    });

    // Send reset link
    if (sendBtn && resetEmail) {
      sendBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const email = resetEmail.value.trim();
        
        // Validate email
        if (!email) {
          resetEmail.classList.add('error');
          resetEmail.placeholder = 'Email is required';
          setTimeout(() => resetEmail.classList.remove('error'), 1000);
          return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          resetEmail.classList.add('error');
          resetEmail.value = '';
          resetEmail.placeholder = 'Please enter a valid email';
          setTimeout(() => resetEmail.classList.remove('error'), 1000);
          return;
        }

        // Show loading state
        sendBtn.disabled = true;
        const btnText = sendBtn.querySelector('.btn-text');
        const btnLoader = sendBtn.querySelector('.btn-loader');
        if (btnText) btnText.classList.add('hidden');
        if (btnLoader) btnLoader.classList.remove('hidden');

        try {
          const response = await fetch('php/forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
          });

          const data = await response.json();
          console.log('Forgot password response:', data);

          // Hide loading state
          sendBtn.disabled = false;
          if (btnText) btnText.classList.remove('hidden');
          if (btnLoader) btnLoader.classList.add('hidden');

          // Close modal
          closeModalFunc();

          // Check if response contains a link
          if (data.success && data.reset_link) {
            if (resetLinkContainer && resetLink) {
              resetLink.href = data.reset_link;
              resetLinkContainer.classList.remove('hidden');
            }
          } else {
            // Show error message
            alert(data.message || 'Email not found in our records');
          }

        } catch (error) {
          console.error('Error:', error);
          
          sendBtn.disabled = false;
          if (btnText) btnText.classList.remove('hidden');
          if (btnLoader) btnLoader.classList.add('hidden');

          alert('Request failed. Please try again.');
        }
      });
    }

    // Reset link close button
    if (resetLinkClose && resetLinkContainer) {
      resetLinkClose.addEventListener('click', function(e) {
        e.preventDefault();
        resetLinkContainer.classList.add('hidden');
      });
    }

    // Enter key on input
    if (resetEmail) {
      resetEmail.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          if (sendBtn) sendBtn.click();
        }
      });

      resetEmail.addEventListener('input', function() {
        this.classList.remove('error');
      });
    }
  }

  // Call forgot password setup
  setupForgotPassword();

  // === SOCIAL BUTTONS (DEMO) ===
  const socialButtons = document.querySelectorAll('.social-btn');
  socialButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      alert('Demo: Social login would connect here!');
    });
  });

  // === REMEMBER ME CHECKBOX ===
  const rememberCheckbox = document.getElementById('rememberMe');
  if (rememberCheckbox) {
    rememberCheckbox.addEventListener('change', function() {
      console.log('Remember me:', this.checked);
    });
  }

  // === QUICK ACTION BUTTONS (DEMO) ===
  const actionBtns = document.querySelectorAll('.action-btn');
  actionBtns.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      alert(`Demo: ${this.textContent.trim()} feature coming soon!`);
    });
  });

  // === LOGOUT ===
  const btnLogout = document.getElementById('btnLogout');
  if (btnLogout) {
    btnLogout.addEventListener('click', function() {
      // Redirect to PHP logout
      window.location.href = 'php/logout.php';
    });
  }

  // === INITIAL STATE ===
  showScreen('landing');
  setAuthMode('signup');
  
  // Initialize strength meter
  if (pwdInput) {
    updatePasswordStrength(pwdInput.value);
  }

}); // End DOMContentLoaded