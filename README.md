# 🏆 Padel Tournament Manager

A modern, offline-first Progressive Web App (PWA) for managing padel tournaments with a team-vs-team format. Built with Laravel, Inertia.js, and Vue 3.

> **2026 with ❤️ from REZIM70**

---

## ✨ Features

### 🎾 Tournament Management
- Create and manage tournaments with customizable settings
- Team vs Team format with multiple pairs per team
- Deterministic cross-team round-robin scheduling
- Pairs from the same team never play each other

### 📊 Live Scoring & Statistics
- Tablet-friendly score input with large touch targets
- Real-time scoreboard with auto-refresh
- TV Mode for large displays (fullscreen, optimized typography)
- Comprehensive statistics dashboard
- Match highlights (highest scoring, closest match, biggest win)

### 🏅 Awards & Recognition
- **MVP Pair** - Highest contribution to team
- **Top Performer** - Best win rate
- **Ironman** - Most matches played
- **Clutch Pair** - Most close victories
- Winning team celebration with confetti animation

### 📈 Leaderboard
- Full pair rankings with sortable columns
- Filter by team
- Track W-L record, points, and point differential

### 📤 Social Sharing
- Generate shareable result cards
- Download as PNG image
- Native share API support

### 📱 Offline-First PWA
- Install as app on any device
- Works offline with IndexedDB storage
- Background sync when connection restored
- Service Worker with smart caching strategies

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 |
| Frontend | Vue 3 + Inertia.js |
| Database | MySQL / SQLite |
| Styling | CSS with dark theme |
| PWA | Service Worker + IndexedDB |
| Build | Vite |

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL (or SQLite for local dev)

### Installation

```bash
# Clone repository
git clone <repository-url>
cd teamrezim

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate

# Build assets
npm run build

# Start server
php artisan serve
```

### Development

```bash
# Run with hot reload
npm run dev

# In another terminal
php artisan serve
```

---

## 📖 Usage

### Creating a Tournament
1. Click **"New Tournament"** from the home page
2. Enter name, description (Court name, Date/Time)
3. Configure pairs per team, rounds, and points per set
4. Add teams and pairs
5. Generate schedule
6. Start tournament!

### Scoring Matches
1. Click on any match from the tournament page
2. Use the score input interface
3. Scores auto-save as you enter them
4. Complete match when finished

### Viewing Results
- **Statistics** - Tournament stats and match highlights
- **Leaderboard** - Pair rankings with sorting
- **Complete** - Winner celebration and awards

---

## 📸 Pages

| Page | Route | Description |
|------|-------|-------------|
| Tournaments | `/tournaments` | List all tournaments |
| Tournament | `/tournaments/{id}` | Tournament detail & management |
| Statistics | `/tournaments/{id}/statistics` | Tournament statistics |
| Leaderboard | `/tournaments/{id}/leaderboard` | Pair rankings |
| Complete | `/tournaments/{id}/complete-view` | Winner & awards |
| Matches | `/tournaments/{id}/matches` | All matches by round |
| Score Input | `/tournaments/{id}/matches/{id}/score` | Enter scores |
| Scoreboard | `/tournaments/{id}/scoreboard` | Live scores |
| TV Mode | `/tournaments/{id}/tv` | Large display view |

---

## 📁 Project Structure

```
app/
├── Http/Controllers/    # Route controllers
├── Models/              # Eloquent models
├── Services/            # Business logic
│   ├── SchedulingService.php
│   ├── ScoringService.php
│   ├── StatisticsService.php
│   └── AwardsService.php
resources/js/
├── Pages/               # Vue page components
├── Components/          # Reusable components
├── Layouts/             # App layout
└── pwa/                 # PWA utilities
public/
├── sw.js                # Service Worker
├── manifest.json        # PWA manifest
└── offline.html         # Offline fallback
```

---

## 📄 License

This project is open-sourced software.
