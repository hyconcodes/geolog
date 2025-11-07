<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'GeoLog') }} - Smart Digital Logbook System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-green': '#10b981',
                        'primary-blue': '#3b82f6',
                        'dark-green': '#059669',
                        'dark-blue': '#2563eb',
                    }
                }
            }
        }
    </script>
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-float-delayed {
            animation: float 6s ease-in-out infinite 2s;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="#" class="flex items-center space-x-2 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-green to-primary-blue rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-book-open text-white text-lg"></i>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-primary-green to-primary-blue bg-clip-text text-transparent">{{ config('app.name', 'GeoLog') }}</span>
                </a>
                
                <!-- Desktop Navigation -->
                <ul class="hidden md:flex items-center space-x-8">
                    <li><a href="#home" class="text-gray-700 hover:text-primary-green font-medium transition-colors">Home</a></li>
                    <li><a href="#features" class="text-gray-700 hover:text-primary-blue font-medium transition-colors">Features</a></li>
                    <li><a href="#benefits" class="text-gray-700 hover:text-primary-green font-medium transition-colors">Benefits</a></li>
                    @auth
                        @if(auth()->user()->hasRole('student'))
                            <li><a href="{{ route('siwes.dashboard') }}" class="text-gray-700 hover:text-primary-green font-medium">Dashboard</a></li>
                        @elseif(auth()->user()->hasRole('supervisor'))
                            <li><a href="{{ route('supervisor.siwes-approvals') }}" class="text-gray-700 hover:text-primary-blue font-medium">Dashboard</a></li>
                        @elseif(auth()->user()->hasRole('superadmin'))
                            <li><a href="{{ route('superadmin.dashboard') }}" class="text-gray-700 hover:text-primary-green font-medium">Dashboard</a></li>
                        @endif
                    @endauth
                </ul>
                
                <!-- Auth Buttons (Desktop) -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-6 py-2 text-primary-green border-2 border-primary-green rounded-full font-semibold hover:bg-primary-green hover:text-white transition-all duration-300">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-2 text-primary-blue border-2 border-primary-blue rounded-full font-semibold hover:bg-primary-blue hover:text-white transition-all duration-300">
                            Login
                        </a>
                        <a href="{{ route('student.register') }}" class="px-6 py-2 bg-gradient-to-r from-primary-green to-primary-blue text-white rounded-full font-semibold hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                            Get Started
                        </a>
                    @endauth
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden text-gray-700 hover:text-primary-green transition-colors">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
                <div class="px-4 py-4 space-y-3">
                    <a href="#home" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-primary-green rounded-lg font-medium transition-colors">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="#features" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary-blue rounded-lg font-medium transition-colors">
                        <i class="fas fa-star mr-2"></i>Features
                    </a>
                    <a href="#benefits" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-primary-green rounded-lg font-medium transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>Benefits
                    </a>
                    @auth
                        @if(auth()->user()->hasRole('student'))
                            <a href="{{ route('siwes.dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-primary-green rounded-lg font-medium transition-colors">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                        @elseif(auth()->user()->hasRole('supervisor'))
                            <a href="{{ route('supervisor.siwes-approvals') }}" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-primary-blue rounded-lg font-medium transition-colors">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                        @elseif(auth()->user()->hasRole('superadmin'))
                            <a href="{{ route('superadmin.dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-primary-green rounded-lg font-medium transition-colors">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                        @endif
                    @endauth
                    
                    <div class="pt-3 border-t border-gray-100 space-y-2">
                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 text-primary-green border-2 border-primary-green rounded-lg font-semibold hover:bg-primary-green hover:text-white transition-all duration-300">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-center text-primary-blue border-2 border-primary-blue rounded-lg font-semibold hover:bg-primary-blue hover:text-white transition-all duration-300">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login
                            </a>
                            <a href="{{ route('student.register') }}" class="block px-4 py-2 text-center bg-gradient-to-r from-primary-green to-primary-blue text-white rounded-lg font-semibold hover:shadow-lg transition-all duration-300">
                                <i class="fas fa-rocket mr-2"></i>Get Started
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="relative min-h-screen flex items-center overflow-hidden bg-gradient-to-br from-blue-50 via-green-50 to-blue-100 pt-16">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 left-10 w-72 h-72 bg-primary-green/10 rounded-full blur-3xl animate-float"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-primary-blue/10 rounded-full blur-3xl animate-float-delayed"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Hero Content -->
                <div class="text-center lg:text-left space-y-8">
                    <div class="inline-block">
                        <span class="px-4 py-2 bg-gradient-to-r from-primary-green/10 to-primary-blue/10 text-primary-blue rounded-full text-sm font-semibold border border-primary-blue/20">
                            <i class="fas fa-rocket mr-2"></i>Next-Gen SIWES Management
                        </span>
                    </div>
                    
                    <h1 class="text-5xl lg:text-6xl font-extrabold leading-tight">
                        <span class="bg-gradient-to-r from-primary-green via-primary-blue to-primary-green bg-clip-text text-transparent">
                            Transform Your
                        </span>
                        <br>
                        <span class="text-gray-900">Industrial Training</span>
                    </h1>
                    
                    <p class="text-xl text-gray-600 leading-relaxed max-w-2xl">
                        Experience seamless SIWES documentation with real-time supervision, GPS verification, and secure cloud storage. Built for Nigerian students and supervisors.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('student.register') }}" class="px-8 py-4 bg-gradient-to-r from-primary-green to-dark-green text-white rounded-full font-bold text-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                            <i class="fas fa-user-graduate mr-2"></i>Start as Student
                        </a>
                        <a href="{{ route('login') }}" class="px-8 py-4 bg-white text-primary-blue border-2 border-primary-blue rounded-full font-bold text-lg hover:bg-primary-blue hover:text-white transition-all duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 pt-8">
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-primary-green">100%</div>
                            <div class="text-sm text-gray-600">Secure</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-primary-blue">24/7</div>
                            <div class="text-sm text-gray-600">Access</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-bold text-primary-green">GPS</div>
                            <div class="text-sm text-gray-600">Verified</div>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Illustration -->
                <div class="relative hidden lg:block">
                    <div class="relative z-10">
                        <div class="bg-white rounded-3xl shadow-2xl p-8 transform hover:scale-105 transition-transform duration-500">
                            <div class="flex items-center space-x-2 mb-6">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            </div>
                            <div class="space-y-4">
                                <div class="h-4 bg-gradient-to-r from-primary-green to-primary-blue rounded w-3/4"></div>
                                <div class="h-4 bg-gray-200 rounded w-full"></div>
                                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                                <div class="grid grid-cols-2 gap-4 mt-6">
                                    <div class="h-24 bg-gradient-to-br from-primary-green/20 to-primary-green/5 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-map-marker-alt text-3xl text-primary-green"></i>
                                    </div>
                                    <div class="h-24 bg-gradient-to-br from-primary-blue/20 to-primary-blue/5 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-3xl text-primary-blue"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Floating Elements -->
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-primary-blue/20 rounded-full blur-2xl animate-float"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-primary-green/20 rounded-full blur-2xl animate-float-delayed"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-extrabold text-gray-900 mb-4">
                    Powerful Features for
                    <span class="bg-gradient-to-r from-primary-green to-primary-blue bg-clip-text text-transparent">Modern SIWES</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Everything you need to manage your industrial training efficiently and securely
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group bg-gradient-to-br from-green-50 to-white p-8 rounded-2xl border-2 border-transparent hover:border-primary-green transition-all duration-300 hover:shadow-xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-green to-dark-green rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-map-marker-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">GPS Verification</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Log activities only from your actual PPA location. Our advanced GPS system ensures authenticity and prevents fraudulent entries.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="group bg-gradient-to-br from-blue-50 to-white p-8 rounded-2xl border-2 border-transparent hover:border-primary-blue transition-all duration-300 hover:shadow-xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-blue to-dark-blue rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-shield-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">2FA Security</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Bank-level security with two-factor authentication. Your logbook data stays protected and accessible only to authorized users.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="group bg-gradient-to-br from-green-50 to-white p-8 rounded-2xl border-2 border-transparent hover:border-primary-green transition-all duration-300 hover:shadow-xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-green to-dark-green rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-clock text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Real-Time Tracking</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Supervisors can monitor student progress in real-time. Instant notifications for submissions and approvals.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="group bg-gradient-to-br from-blue-50 to-white p-8 rounded-2xl border-2 border-transparent hover:border-primary-blue transition-all duration-300 hover:shadow-xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-blue to-dark-blue rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Digital Reports</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Generate comprehensive reports automatically. Export to PDF with professional formatting and institutional branding.
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="group bg-gradient-to-br from-green-50 to-white p-8 rounded-2xl border-2 border-transparent hover:border-primary-green transition-all duration-300 hover:shadow-xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-green to-dark-green rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Multi-Role Access</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Separate dashboards for students, supervisors, and administrators. Role-based permissions for enhanced security.
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="group bg-gradient-to-br from-blue-50 to-white p-8 rounded-2xl border-2 border-transparent hover:border-primary-blue transition-all duration-300 hover:shadow-xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-blue to-dark-blue rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-chart-line text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Analytics Dashboard</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Track your progress with visual analytics. View weekly summaries, activity trends, and performance metrics.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl lg:text-5xl font-extrabold text-gray-900 mb-6">
                        Why Students & Supervisors
                        <span class="bg-gradient-to-r from-primary-green to-primary-blue bg-clip-text text-transparent">Love {{ config('app.name', 'GeoLog') }}</span>
                    </h2>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-primary-green rounded-xl flex items-center justify-center">
                                <i class="fas fa-check text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">No More Paper Logbooks</h3>
                                <p class="text-gray-600">Say goodbye to lost or damaged physical logbooks. Everything is securely stored in the cloud.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-primary-blue rounded-xl flex items-center justify-center">
                                <i class="fas fa-check text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Instant Supervisor Feedback</h3>
                                <p class="text-gray-600">Get real-time comments and approvals from your supervisor without physical meetings.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-primary-green rounded-xl flex items-center justify-center">
                                <i class="fas fa-check text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Verified Authenticity</h3>
                                <p class="text-gray-600">GPS verification ensures all entries are genuine and made from your actual workplace.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-primary-blue rounded-xl flex items-center justify-center">
                                <i class="fas fa-check text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Easy Report Generation</h3>
                                <p class="text-gray-600">Generate professional final reports with one click. Export to PDF for submission.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="bg-white rounded-3xl shadow-2xl p-8">
                        <div class="space-y-6">
                            <div class="flex items-center space-x-4 p-4 bg-green-50 rounded-xl">
                                <div class="w-12 h-12 bg-primary-green rounded-full flex items-center justify-center">
                                    <i class="fas fa-graduation-cap text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Student Dashboard</div>
                                    <div class="text-sm text-gray-600">Log activities & track progress</div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4 p-4 bg-blue-50 rounded-xl">
                                <div class="w-12 h-12 bg-primary-blue rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-tie text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Supervisor Portal</div>
                                    <div class="text-sm text-gray-600">Review & approve submissions</div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4 p-4 bg-green-50 rounded-xl">
                                <div class="w-12 h-12 bg-primary-green rounded-full flex items-center justify-center">
                                    <i class="fas fa-shield-alt text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Secure & Private</div>
                                    <div class="text-sm text-gray-600">2FA & encrypted storage</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-primary-green via-primary-blue to-primary-green">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-extrabold text-white mb-6">
                Ready to Digitalize Your SIWES?
            </h2>
            <p class="text-xl text-white/90 mb-10 max-w-2xl mx-auto">
                Join hundreds of students and supervisors already using {{ config('app.name', 'GeoLog') }} for seamless industrial training management.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('student.register') }}" class="px-10 py-4 bg-white text-primary-green rounded-full font-bold text-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                    <i class="fas fa-rocket mr-2"></i>Get Started Free
                </a>
                <a href="{{ route('login') }}" class="px-10 py-4 bg-transparent text-white border-2 border-white rounded-full font-bold text-lg hover:bg-white hover:text-primary-blue transition-all duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-green to-primary-blue rounded-xl flex items-center justify-center">
                            <i class="fas fa-book-open text-white"></i>
                        </div>
                        <span class="text-2xl font-bold">{{ config('app.name', 'GeoLog') }}</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        {{ config('app.name', 'GeoLog') }} - Smart Digital Logbook System for SIWES management. Built with care by students of BOUESTI.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary-green transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary-blue transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary-green transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="font-bold text-lg mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#home" class="hover:text-primary-green transition-colors">Home</a></li>
                        <li><a href="#features" class="hover:text-primary-blue transition-colors">Features</a></li>
                        <li><a href="#benefits" class="hover:text-primary-green transition-colors">Benefits</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-primary-blue transition-colors">Login</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold text-lg mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-primary-green transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-primary-blue transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-primary-green transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-primary-blue transition-colors">Contact Us</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; {{ date("Y") }} {{ config('app.name', 'GeoLog') }} - Smart Digital Logbook System. Built with <i class="fas fa-heart text-red-500"></i> by Students of BOUESTI.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                    const icon = mobileMenuButton.querySelector('i');
                    if (mobileMenu.classList.contains('hidden')) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    } else {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    }
                });
                
                // Close mobile menu when clicking on a link
                document.querySelectorAll('#mobile-menu a').forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                        const icon = mobileMenuButton.querySelector('i');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    });
                });
                
                // Close mobile menu on window resize
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 768) {
                        mobileMenu.classList.add('hidden');
                        const icon = mobileMenuButton.querySelector('i');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });
            }
            
            // Smooth scroll
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            // Header scroll effect
            window.addEventListener('scroll', () => {
                const header = document.querySelector('header');
                if (header && window.scrollY > 50) {
                    header.classList.add('shadow-lg');
                } else if (header) {
                    header.classList.remove('shadow-lg');
                }
            });
        });
    </script>
</body>
</html>
