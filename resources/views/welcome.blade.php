<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LogX - Smart Digital Logbook System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-green: #008037;
            --secondary-gold: #FFD700;
            --background: #F8FAF9;
            --text-dark: #1C1C1C;
            --text-muted: #6B6B6B;
            --white: #FFFFFF;
            --shadow: rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 128, 55, 0.1);
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-green);
            text-decoration: none;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-gold));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }
        
        .nav-links a:hover {
            color: var(--primary-green);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-green);
            transition: width 0.3s ease;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .auth-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-outline {
            color: var(--primary-green);
            border-color: var(--primary-green);
            background: transparent;
        }
        
        .btn-outline:hover {
            background: var(--primary-green);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--shadow);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-gold), #FFA500);
            color: var(--text-dark);
            border-color: var(--secondary-gold);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-green) 0%, #00A040 50%, var(--secondary-gold) 100%);
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="300" r="150" fill="url(%23a)"/><circle cx="800" cy="200" r="200" fill="url(%23a)"/><circle cx="600" cy="700" r="180" fill="url(%23a)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        
        .btn-hero-primary {
            background: var(--secondary-gold);
            color: var(--text-dark);
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }
        
        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255, 215, 0, 0.4);
        }
        
        .btn-hero-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-hero-secondary:hover {
            background: white;
            color: var(--primary-green);
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255, 255, 255, 0.3);
        }
        
        .hero-illustration {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .floating-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape-1 {
            width: 200px;
            height: 200px;
            top: -50px;
            right: -50px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 150px;
            height: 150px;
            bottom: -30px;
            left: -30px;
            animation-delay: 2s;
        }
        
        .shape-3 {
            width: 100px;
            height: 100px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .mockup-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 3;
        }
        
        .mockup-screen {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .mockup-header {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .mockup-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .dot-red { background: #ff5f57; }
        .dot-yellow { background: #ffbd2e; }
        .dot-green { background: #28ca42; }
        
        .mockup-content {
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 1.1rem;
        }
        
        /* Features Section */
        .features {
            padding: 6rem 0;
            background: var(--white);
        }
        
        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            border: 1px solid rgba(0, 128, 55, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-green), var(--secondary-gold));
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px var(--shadow);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-gold));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        
        .feature-card p {
            color: var(--text-muted);
            line-height: 1.6;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Footer */
        .footer {
            background: var(--primary-green);
            color: white;
            padding: 3rem 0 2rem;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            text-align: center;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }
        
        .footer-links a {
            color: var(--secondary-gold);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 215, 0, 0.3);
            padding-top: 2rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
            cursor: pointer;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero-container {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-links {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .nav-container {
                padding: 0 1rem;
            }
            
            .hero-container {
                padding: 0 1rem;
            }
            
            .features-container {
                padding: 0 1rem;
            }
            
            .footer-container {
                padding: 0 1rem;
            }
            
            .auth-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
        }
    </style>
    
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav-container">
            <a href="#" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-book"></i>
                </div>
                <span>LogX</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="{{ url('/#home') }}">Home</a></li>
                <li><a href="{{ url('/#about') }}">About</a></li>
                <li><a href="{{ url('/#features') }}">Features</a></li>
                <li><a href="{{ url('/#contact') }}">Contact</a></li>
                @auth
                    @if(auth()->user()->hasRole('student'))
                        <li><a href="{{ route('siwes.dashboard') }}">Student Dashboard</a></li>
                    @elseif(auth()->user()->hasRole('supervisor'))
                        <li><a href="{{ route('supervisor.siwes-approvals') }}">Supervisor Dashboard</a></li>
                    @elseif(auth()->user()->hasRole('superadmin'))
                        <li><a href="{{ route('superadmin.dashboard') }}">Admin Dashboard</a></li>
                    @endif
                @endauth
            </ul>
            
            <div class="auth-buttons">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="btn btn-outline" 
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            Logout
                        </a>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
                    <div class="dropdown" style="display: inline-block;">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="registerDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Register
                        </button>
                        {{-- <ul class="dropdown-menu" aria-labelledby="registerDropdown">
                            <li><a class="dropdown-item" href="{{ route('student.register') }}">As Student</a></li>
                            <li><a class="dropdown-item" href="{{ route('supervisor.register') }}">As Supervisor</a></li>
                        </ul> --}}
                    </div>
                @endauth
            </div>
            
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home" style="padding-top: 40px">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Transforming Industrial Training into Smart Digital Experience</h1>
                <p>Record, Track, and Verify your SIWES journey with real-time supervision, geolocation, and offline accessibility.</p>
                <div class="hero-buttons">
                    <a href="{{ route('student.register') }}" class="btn-hero-primary">Get Started</a>
                    <a href="{{ route('login') }}" class="btn-hero-secondary">Login as Student / Supervisor</a>
                </div>
            </div>
            
            <div class="hero-illustration">
                <div class="floating-shape shape-1"></div>
                <div class="floating-shape shape-2"></div>
                <div class="floating-shape shape-3"></div>
                
                <div class="mockup-container">
                    <div class="mockup-screen">
                        <div class="mockup-header">
                            <div class="mockup-dot dot-red"></div>
                            <div class="mockup-dot dot-yellow"></div>
                            <div class="mockup-dot dot-green"></div>
                        </div>
                        <div class="mockup-content">
                            <i class="fas fa-laptop" style="font-size: 3rem; color: var(--primary-green);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-container">
            <h2 class="section-title">Why Choose LogX?</h2>
            <p class="section-subtitle">Experience the future of industrial training documentation with our cutting-edge features designed for students and supervisors.</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Geo-Verified Logs</h3>
                    <p>Log your activities only from your real PPA location with GPS verification, ensuring authenticity and preventing fraudulent entries.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>2FA Security</h3>
                    <p>Protect your account with two-factor authentication, ensuring your logbook data remains secure and accessible only to you.</p>
                </div>
                
                {{-- <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h3>Offline Sync</h3>
                    <p>Record your logs even without internet connection. Data automatically syncs when you're back online, ensuring no entry is lost.</p>
                </div> --}}
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-logo">
                    <div class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <span style="color: white;">LogX</span>
                    </div>
                </div>
                
                <ul class="footer-links">
                    <li><a href="#privacy">Privacy Policy</a></li>
                    <li><a href="#support">Contact Support</a></li>
                    <li><a href="#terms">Terms of Service</a></li>
                </ul>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Smart Digital Logbook System (LogX). Built with 💖 by Students of BOUESTI.</p>
            </div>
        </div>
    </footer>

    <script>
        // Add smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = 'none';
            }
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe feature cards
        document.querySelectorAll('.feature-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
