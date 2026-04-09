<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0a0a0f;
            --bg-secondary: #111118;
            --bg-card: #151520;
            --accent-1: #3b82f6;
            --accent-2: #8b5cf6;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --border-color: #2a2a35;
            --glass-bg: rgba(255, 255, 255, 0.03);
        }

        * {
            font-family: 'Inter', -apple-system, sans-serif;
            box-sizing: border-box;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .profile-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 1rem;
        }

        .profile-card {
            background: var(--glass-bg);
            backdrop-filter: blur(40px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            max-width: 480px;
            margin: 0 auto;
            overflow: hidden;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .profile-card:hover {
            transform: translateY(-8px);
            box-shadow: 
                0 35px 70px -12px rgba(0, 0, 0, 0.6),
                0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        .profile-header {
            padding: 3rem 2.5rem 2rem;
            text-align: center;
            position: relative;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
            border-bottom: 1px solid var(--border-color);
        }

        .avatar-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.1);
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.4),
                inset 0 0 0 1px rgba(255, 255, 255, 0.1);
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .avatar-wrapper:hover .avatar {
            transform: scale(1.05);
            box-shadow: 
                0 20px 40px rgba(59, 130, 246, 0.3),
                inset 0 0 0 1px rgba(59, 130, 246, 0.3);
        }

        .status-indicator {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 20px;
            height: 20px;
            background: #10b981;
            border: 3px solid var(--bg-card);
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .display-name {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .username {
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding: 1rem 0;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: block;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .profile-content {
            padding: 2rem 2.5rem 2.5rem;
        }

        .info-grid {
            display: grid;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .info-row:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateX(4px);
        }

        .info-icon {
            width: 44px;
            height: 44px;
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent-1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-right: 1.25rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .info-row:hover .info-icon {
            background: rgba(59, 130, 246, 0.4);
            transform: scale(1.05);
        }

        .info-text h6 {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin: 0 0 0.25rem 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-text p {
            margin: 0;
            font-weight: 500;
            font-size: 1rem;
        }

        .info-text .empty {
            color: #64748b;
            font-style: italic;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-modern {
            flex: 1;
            padding: 12px 24px;
            border: 1px solid var(--border-color);
            background: transparent;
            color: var(--text-primary);
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary-modern {
            border-color: var(--accent-1);
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-1);
        }

        .btn-primary-modern:hover {
            background: var(--accent-1);
            color: var(--bg-card);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary-modern {
            color: var(--text-secondary);
        }

        .btn-secondary-modern:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            transform: translateY(-2px);
        }

        @media (max-width: 576px) {
            .profile-stats {
                gap: 1rem;
            }
            
            .stat-value {
                font-size: 1.25rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="profile-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 col-12">
                    <div class="profile-card">
                        <!-- Header -->
                        <div class="profile-header">
                            <div class="avatar-wrapper">
                                <img src="https://ui-avatars.com/api/?name={{ $user->name }}&size=100&background=1e293b&color=f8fafc&font-size=0.6&bold=true" 
                                     alt="{{ $user->name }}" class="avatar">
                                <div class="status-indicator"></div>
                            </div>
                            <h1 class="display-name">{{ $user->name }}</h1>
                           <p class="username"> {{ $user->role->name }}</p>
                        </div>

                        <!-- Content -->
                        <div class="profile-content">
                            <div class="info-grid">
                                <div class="info-row">
                                    <div class="info-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="info-text">
                                        <h6>Email</h6>
                                        <p>{{ $user->email }}</p>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="info-text">
                                        <h6>Phone</h6>
                                        <p class="{{ $user->phone ? '' : 'empty' }}">
                                            {{ $user->phone ?? 'Not provided' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="info-row">
                                    <div class="info-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-text">
                                        <h6>Member since</h6>
                                        <p>{{ $user->created_at->format('M Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>