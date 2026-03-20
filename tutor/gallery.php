<?php
require_once('includes/init.php');
include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/headtag.php' ?>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap"
        rel="stylesheet">
    <style>
        /* ════════════════════════════════
           TOKENS
        ════════════════════════════════ */
        :root {
            --blue: #41A141;
            --blue-lt: #e8f5e9;
            --dark: #1a1d23;
            --body-clr: #495057;
            --muted: #868e96;
            --border: #e9ecef;
            --bg: #f8faf9;
            --white: #ffffff;
            --radius: 12px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, .06);
            --shadow-md: 0 8px 28px rgba(0, 0, 0, .10);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, .18);
            --ease: cubic-bezier(.4, 0, .2, 1);
        }

        body {
            background: var(--bg);
            font-family: 'DM Sans', sans-serif;
            color: var(--body-clr);
        }

        a {
            color: var(--blue);
        }

        a:hover {
            color: #2a6a2a;
        }

        /* ════════════════════════════════
           PAGE HERO
        ════════════════════════════════ */
        .gh-hero {
            background: linear-gradient(135deg, #1b431b 0%, #2a6a2a 50%, #41A141 100%);
            padding: 52px 0 68px;
            position: relative;
            overflow: hidden;
        }

        .gh-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 60% 50% at 90% 110%, rgba(255, 255, 255, .1) 0%, transparent 65%),
                url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='.03'%3E%3Ccircle cx='40' cy='40' r='30'/%3E%3Ccircle cx='40' cy='40' r='18'/%3E%3C/g%3E%3C/svg%3E");
        }

        .gh-hero .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .18);
            color: #c8e6c9;
            font-size: .72rem;
            font-weight: 500;
            letter-spacing: .14em;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 50px;
            margin-bottom: 18px;
        }

        .gh-hero h1 {
            color: #fff;
            font-size: clamp(1.8rem, 3vw, 2.8rem);
            font-weight: 800;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .gh-hero p {
            color: #e8f5e9;
            font-size: .95rem;
            max-width: 480px;
            margin: 0;
        }

        /* ════════════════════════════════
           FILTER BAR
        ════════════════════════════════ */
        .gh-filters {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: var(--shadow-sm);
        }

        .gh-filters-inner {
            display: flex;
            gap: 8px;
            padding: 13px 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .gh-filters-inner::-webkit-scrollbar {
            display: none;
        }

        .f-pill {
            flex-shrink: 0;
            border: 1.5px solid var(--border);
            background: var(--bg);
            color: var(--muted);
            font-size: .8rem;
            font-weight: 500;
            line-height: 1;
            padding: 7px 16px;
            border-radius: 50px;
            cursor: pointer;
            transition: all .22s var(--ease);
            white-space: nowrap;
            user-select: none;
        }

        .f-pill:hover {
            border-color: var(--blue);
            color: var(--blue);
            background: var(--blue-lt);
        }

        .f-pill.active {
            background: var(--blue);
            border-color: var(--blue);
            color: #fff;
        }

        /* ════════════════════════════════
           CONTENT AREA
        ════════════════════════════════ */
        .gh-body {
            padding: 32px 0 60px;
        }

        /* counters strip */
        .gh-meta-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }

        .gh-count-txt {
            font-size: .83rem;
            color: var(--muted);
        }

        .gh-count-txt strong {
            color: var(--dark);
        }

        /* ════════════════════════════════
           GRID
        ════════════════════════════════ */
        .g-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(255px, 1fr));
            gap: 22px;
        }

        /* ════════════════════════════════
           CARD
        ════════════════════════════════ */
        .g-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            overflow: hidden;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: transform .28s var(--ease), box-shadow .28s var(--ease), border-color .28s;
            /* entrance */
            opacity: 0;
            transform: translateY(20px);
            animation: cardIn .45s var(--ease) forwards;
        }

        .g-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
            border-color: rgba(65, 161, 65, .18);
        }

        @keyframes cardIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .g-card:nth-child(1) {
            animation-delay: .04s
        }

        .g-card:nth-child(2) {
            animation-delay: .08s
        }

        .g-card:nth-child(3) {
            animation-delay: .12s
        }

        .g-card:nth-child(4) {
            animation-delay: .16s
        }

        .g-card:nth-child(5) {
            animation-delay: .20s
        }

        .g-card:nth-child(6) {
            animation-delay: .24s
        }

        .g-card:nth-child(7) {
            animation-delay: .28s
        }

        .g-card:nth-child(8) {
            animation-delay: .32s
        }

        .g-card:nth-child(9) {
            animation-delay: .36s
        }

        .g-card:nth-child(10) {
            animation-delay: .40s
        }

        .g-card:nth-child(11) {
            animation-delay: .44s
        }

        .g-card:nth-child(12) {
            animation-delay: .48s
        }

        /* thumbnail */
        .g-thumb {
            position: relative;
            height: 188px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .g-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .5s var(--ease);
        }

        .g-card:hover .g-thumb img {
            transform: scale(1.08);
        }

        /* icon placeholder colours */
        .g-thumb {
            background: #f1f3f1;
        }

        .g-thumb.cat-celebration {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.15));
        }

        .g-thumb.cat-sports {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.2));
        }

        .g-thumb.cat-cultural {
            background: linear-gradient(140deg, #f1f8f1, rgba(65, 161, 65, 0.12));
        }

        .g-thumb.cat-workshop {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.25));
        }

        .g-thumb.cat-achievement {
            background: linear-gradient(140deg, #e8f5e9, rgba(34, 84, 34, 0.1));
        }

        .g-thumb.cat-trip {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.18));
        }

        .g-thumb.cat-event {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.22));
        }

        .g-thumb-icon {
            font-size: 3.2rem;
            line-height: 1;
            opacity: .65;
        }

        .g-thumb.cat-celebration .g-thumb-icon,
        .g-thumb.cat-sports .g-thumb-icon,
        .g-thumb.cat-cultural .g-thumb-icon,
        .g-thumb.cat-workshop .g-thumb-icon,
        .g-thumb.cat-achievement .g-thumb-icon,
        .g-thumb.cat-trip .g-thumb-icon,
        .g-thumb.cat-event .g-thumb-icon {
            color: var(--blue);
        }

        /* hover overlay */
        .g-overlay {
            position: absolute;
            inset: 0;
            background: rgba(30, 70, 30, .42);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .28s var(--ease);
        }

        .g-overlay i {
            color: #fff;
            font-size: 1.5rem;
        }

        .g-card:hover .g-overlay {
            opacity: 1;
        }

        /* badge on thumb */
        .g-thumb-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #41A141;
            background: rgba(255, 255, 255, .9);
            backdrop-filter: blur(4px);
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            border-radius: 50px;
            padding: 3px 10px;
            line-height: 1.6;
        }

        /* card body */
        .g-body {
            padding: 15px 17px 17px;
        }

        .g-title {
            font-size: .98rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 7px;
            line-height: 1.35;
        }

        .g-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .76rem;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .g-meta i {
            color: var(--blue);
            font-size: .7rem;
        }

        .g-desc {
            font-size: .8rem;
            color: #868e96;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ════════════════════════════════
           LIGHTBOX
        ════════════════════════════════ */
        #lb-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1200;
            background: rgba(10, 30, 15, .86);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            align-items: center;
            justify-content: center;
        }

        #lb-backdrop.open {
            display: flex;
        }

        #lb-box {
            background: var(--white);
            border-radius: 16px;
            max-width: 760px;
            width: 92%;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            animation: lbOpen .28s var(--ease);
            position: relative;
        }

        @keyframes lbOpen {
            from {
                opacity: 0;
                transform: scale(.92) translateY(16px);
            }
        }

        #lb-media img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            display: block;
        }

        .lb-icon-area {
            height: 260px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5.5rem;
        }

        .lb-icon-area.cat-celebration {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.15));
            color: var(--blue);
        }

        .lb-icon-area.cat-sports {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.2));
            color: var(--blue);
        }

        .lb-icon-area.cat-cultural {
            background: linear-gradient(140deg, #f1f8f1, rgba(65, 161, 65, 0.12));
            color: var(--blue);
        }

        .lb-icon-area.cat-workshop {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.2));
            color: var(--blue);
        }

        .lb-icon-area.cat-achievement {
            background: linear-gradient(140deg, #e8f5e9, rgba(34, 84, 34, 0.1));
            color: var(--blue);
        }

        .lb-icon-area.cat-trip {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.18));
            color: var(--blue);
        }

        .lb-icon-area.cat-event {
            background: linear-gradient(140deg, #e8f5e9, rgba(65, 161, 65, 0.22));
            color: var(--blue);
        }

        #lb-info {
            padding: 22px 26px 26px;
        }

        #lb-cat {
            display: inline-block;
            background: var(--blue-lt);
            color: var(--blue);
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 50px;
            margin-bottom: 10px;
        }

        #lb-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 6px;
        }

        #lb-date {
            font-size: .8rem;
            color: var(--muted);
            margin-bottom: 12px;
        }

        #lb-desc {
            font-size: .88rem;
            color: var(--body-clr);
            line-height: 1.75;
        }

        /* nav / close buttons */
        .lb-btn {
            position: absolute;
            border: none;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            border-radius: 50%;
            width: 42px;
            height: 42px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: background .2s;
        }

        .lb-btn:hover {
            background: rgba(255, 255, 255, .28);
        }

        #lb-close {
            top: 16px;
            right: 18px;
        }

        #lb-prev {
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
        }

        #lb-next {
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
        }

        /* ════════════════════════════════
           EMPTY STATE
        ════════════════════════════════ */
        .g-empty {
            display: none;
            text-align: center;
            padding: 64px 20px;
            color: var(--muted);
        }

        .g-empty i {
            font-size: 3.5rem;
            margin-bottom: 16px;
            display: block;
            color: #dee2e6;
        }

        .g-empty h5 {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 6px;
        }

        @media (max-width: 576px) {

            #lb-prev,
            #lb-next {
                display: none;
            }
        }
    </style>
</head>

<body class="app">
    <?php include 'includes/header.php' ?>

    <div class="app-wrapper">

        <!-- ═══ HERO ═══ -->
        <div class="gh-hero">
            <div class="container-xl px-4">
                <div class="eyebrow"><i class="fas fa-images fa-xs"></i> Our Memories</div>
                <h1>Events &amp; Gallery</h1>
                <p>Moments from celebrations, workshops, sports days, cultural programs, and more — all in one place.
                </p>
            </div>
        </div>

        <!-- ═══ FILTER BAR ═══ -->
        <div class="gh-filters">
            <div class="container-xl px-4">
                <div class="gh-filters-inner">
                    <button class="f-pill active" data-filter="all">All</button>
                    <button class="f-pill" data-filter="celebration">🎉 Celebrations</button>
                    <button class="f-pill" data-filter="event">📅 Events</button>
                    <button class="f-pill" data-filter="sports">🏆 Sports</button>
                    <button class="f-pill" data-filter="cultural">🎭 Cultural</button>
                    <button class="f-pill" data-filter="workshop">📚 Workshops</button>
                    <button class="f-pill" data-filter="achievement">🥇 Achievements</button>
                    <button class="f-pill" data-filter="trip">🚌 Trips</button>
                </div>
            </div>
        </div>

        <!-- ═══ BODY ═══ -->
        <div class="gh-body">
            <div class="container-xl px-4">

                <div class="gh-meta-strip">
                    <p class="gh-count-txt mb-0">Showing <strong id="visibleCount">12</strong> of <strong>12</strong>
                        items</p>
                </div>

                <!-- ═══ GRID ═══ -->
                <div class="g-grid" id="galleryGrid">

                    <!-- ─── 1. Annual Prize Distribution ─── -->
                    <div class="g-card" data-category="celebration" data-title="Annual Prize Distribution"
                        data-date="15 January 2025" data-cat-label="🎉 Celebration" data-icon="fas fa-trophy"
                        data-color="cat-celebration"
                        data-desc="A grand ceremony celebrating student achievements with trophies, medals, and certificates awarded across all academic levels. Parents and guests joined the proud moment.">
                        <div class="g-thumb cat-celebration">
                            <i class="g-thumb-icon fas fa-trophy"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge">🎉 Celebration</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Annual Prize Distribution</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 15 January 2025</div>
                            <div class="g-desc">Grand ceremony celebrating student achievements with trophies, medals,
                                and certificates across all academic levels.</div>
                        </div>
                    </div>

                    <!-- ─── 2. Sports Day ─── -->
                    <div class="g-card" data-category="sports" data-title="Annual Sports Day 2025"
                        data-date="20 February 2025" data-cat-label="🏆 Sports" data-icon="fas fa-running"
                        data-color="cat-sports"
                        data-desc="An action-packed day filled with track races, tug of war, relay, long jump, and exciting inter-house competitions. Energy and team spirit were at an all-time high.">
                        <div class="g-thumb cat-sports">
                            <i class="g-thumb-icon fas fa-running"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge">🏆 Sports</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Annual Sports Day 2025</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 20 February 2025</div>
                            <div class="g-desc">Track races, tug of war, relay, and exciting inter-house competitions
                                full of energy and team spirit.</div>
                        </div>
                    </div>

                    <!-- ─── 3. Republic Day Cultural ─── -->
                    <div class="g-card" data-category="cultural" data-title="Cultural Evening — Colours of India"
                        data-date="26 January 2025" data-cat-label="🎭 Cultural" data-icon="fas fa-theater-masks"
                        data-color="cat-cultural"
                        data-desc="Students performed classical dances, folk music, drama, and showcased traditional attire from across India on Republic Day. A colourful tribute to national unity.">
                        <div class="g-thumb cat-cultural">
                            <i class="g-thumb-icon fas fa-theater-masks"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge">🎭 Cultural</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Cultural Evening — Colours of India</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 26 January 2025</div>
                            <div class="g-desc">Classical dances, folk music, drama, and traditional attire from across
                                India on Republic Day.</div>
                        </div>
                    </div>

                    <!-- ─── 4. Science Exhibition ─── -->
                    <div class="g-card" data-category="workshop" data-title="Science Exhibition Workshop"
                        data-date="8 March 2025" data-cat-label="📚 Workshop" data-icon="fas fa-flask"
                        data-color="cat-workshop"
                        data-desc="Students displayed innovative science projects, working models, and experiments judged by a panel of external experts and senior teachers. Creativity met curiosity.">
                        <div class="g-thumb cat-workshop">
                            <i class="g-thumb-icon fas fa-flask"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge">📚 Workshop</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Science Exhibition Workshop</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 8 March 2025</div>
                            <div class="g-desc">Innovative projects, models, and experiments judged by external experts.
                                Creativity met curiosity.</div>
                        </div>
                    </div>

                    <!-- ─── 5. Museum Trip ─── -->
                    <div class="g-card" data-category="trip" data-title="Educational Trip — Ahmedabad"
                        data-date="22 March 2025" data-cat-label="🚌 Trip" data-icon="fas fa-bus" data-color="cat-trip"
                        data-desc="Students visited Science City and the Natural History Museum, exploring hands-on exhibits that brought classroom learning to life in the most vivid way.">
                        <div class="g-thumb cat-trip">
                            <i class="g-thumb-icon fas fa-bus"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge" style="color: #00897b">🚌 Trip</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Educational Trip — Ahmedabad</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 22 March 2025</div>
                            <div class="g-desc">Science City and Natural History Museum visit — classroom learning
                                brought vividly to life.</div>
                        </div>
                    </div>

                    <!-- ─── 6. Board Toppers ─── -->
                    <div class="g-card" data-category="achievement" data-title="Board Toppers Felicitation"
                        data-date="10 June 2025" data-cat-label="🥇 Achievement" data-icon="fas fa-medal"
                        data-color="cat-achievement"
                        data-desc="Board exam toppers were felicitated at a special ceremony attended by parents, senior staff, and education board representatives. An evening of pride and inspiration.">
                        <div class="g-thumb cat-achievement">
                            <i class="g-thumb-icon fas fa-medal"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge" style="color:#6741d9">🥇 Achievement</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Board Toppers Felicitation</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 10 June 2025</div>
                            <div class="g-desc">Special ceremony attended by parents, staff, and education board
                                representatives — an evening of pride.</div>
                        </div>
                    </div>

                    <!-- ─── 7. Teacher's Day ─── -->
                    <div class="g-card" data-category="celebration" data-title="Teacher's Day Celebration"
                        data-date="5 September 2025" data-cat-label="🎉 Celebration"
                        data-icon="fas fa-chalkboard-teacher" data-color="cat-celebration"
                        data-desc="Students honoured their teachers with heartfelt performances, handmade cards, speeches, and a special felicitation ceremony. A day of gratitude and warmth.">
                        <div class="g-thumb cat-celebration">
                            <i class="g-thumb-icon fas fa-chalkboard-teacher"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge text-warning">🎉 Celebration</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Teacher's Day Celebration</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 5 September 2025</div>
                            <div class="g-desc">Performances, handmade cards, speeches, and a special felicitation
                                ceremony — a day of gratitude.</div>
                        </div>
                    </div>

                    <!-- ─── 8. Founders Day ─── -->
                    <div class="g-card" data-category="event" data-title="Annual Founders' Day"
                        data-date="1 October 2025" data-cat-label="📅 Event" data-icon="fas fa-landmark"
                        data-color="cat-event"
                        data-desc="Commemorating the institution's founding with a formal assembly, keynote address by the principal, guest lectures, student showcases, and a celebratory gala dinner.">
                        <div class="g-thumb cat-event">
                            <i class="g-thumb-icon fas fa-landmark"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge">📅 Event</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Annual Founders' Day</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 1 October 2025</div>
                            <div class="g-desc">Formal assembly, keynote address, guest lectures, student showcases, and
                                a celebratory gala dinner.</div>
                        </div>
                    </div>

                    <!-- ─── 9. Diwali ─── -->
                    <div class="g-card" data-category="celebration" data-title="Diwali Celebration &amp; Rangoli"
                        data-date="28 October 2025" data-cat-label="🎉 Celebration" data-icon="fas fa-fire-alt"
                        data-color="cat-celebration"
                        data-desc="The campus lit up with oil diyas, vibrant rangoli competitions, cultural performances, devotional songs, and a festive Diwali mela enjoyed by all students and families.">
                        <div class="g-thumb cat-celebration">
                            <i class="g-thumb-icon fas fa-fire-alt"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge text-warning">🎉 Celebration</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Diwali Celebration &amp; Rangoli</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 28 October 2025</div>
                            <div class="g-desc">Diyas, rangoli, cultural performances, and a vibrant Diwali mela enjoyed
                                by all students and families.</div>
                        </div>
                    </div>

                    <!-- ─── 10. Cricket ─── -->
                    <div class="g-card" data-category="sports" data-title="Inter-School Cricket Tournament"
                        data-date="14 November 2025" data-cat-label="🏆 Sports" data-icon="fas fa-baseball-ball"
                        data-color="cat-sports"
                        data-desc="Our school hosted the zonal inter-school cricket tournament with 12 competing schools. Our team played with great spirit and emerged as proud runners-up of the competition.">
                        <div class="g-thumb cat-sports">
                            <i class="g-thumb-icon fas fa-baseball-ball"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge text-success">🏆 Sports</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Inter-School Cricket Tournament</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 14 November 2025</div>
                            <div class="g-desc">Zonal tournament with 12 schools — our team emerged proud runners-up
                                with exceptional spirit.</div>
                        </div>
                    </div>

                    <!-- ─── 11. Coding Bootcamp ─── -->
                    <div class="g-card" data-category="workshop" data-title="Coding Bootcamp — Python Basics"
                        data-date="12 December 2025" data-cat-label="📚 Workshop" data-icon="fas fa-code"
                        data-color="cat-workshop"
                        data-desc="A two-day intensive coding workshop where students learned Python fundamentals, built mini-projects, and showcased their work to an audience of peers, teachers, and parents.">
                        <div class="g-thumb cat-workshop">
                            <i class="g-thumb-icon fas fa-code"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge">📚 Workshop</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Coding Bootcamp — Python Basics</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 12 December 2025</div>
                            <div class="g-desc">Two-day intensive: students learned Python, built mini-projects, and
                                showcased work to teachers and parents.</div>
                        </div>
                    </div>

                    <!-- ─── 12. Christmas ─── -->
                    <div class="g-card" data-category="cultural" data-title="Christmas &amp; New Year Celebration"
                        data-date="24 December 2025" data-cat-label="🎭 Cultural" data-icon="fas fa-snowflake"
                        data-color="cat-cultural"
                        data-desc="Secret Santa gift exchange, carol singing, cake-cutting, classroom decorating competition, and a joyful countdown to ring in the festive season with cheer and laughter.">
                        <div class="g-thumb cat-cultural">
                            <i class="g-thumb-icon fas fa-snowflake"></i>
                            <div class="g-overlay"><i class="fas fa-expand-alt"></i></div>
                            <span class="g-thumb-badge text-danger">🎭 Cultural</span>
                        </div>
                        <div class="g-body">
                            <div class="g-title">Christmas &amp; New Year Celebration</div>
                            <div class="g-meta"><i class="fas fa-calendar-alt"></i> 24 December 2025</div>
                            <div class="g-desc">Secret Santa, carol singing, cake-cutting, and a joyful classroom
                                decoration competition.</div>
                        </div>
                    </div>

                </div><!-- /g-grid -->

                <!-- Empty state -->
                <div class="g-empty" id="emptyState">
                    <i class="fas fa-images"></i>
                    <h5>No items in this category</h5>
                    <p class="small">Choose a different filter above to browse other events.</p>
                </div>

            </div>
        </div>

    </div><!--//app-wrapper-->

    <!-- ════════════════ LIGHTBOX ════════════════ -->
    <div id="lb-backdrop">
        <button class="lb-btn" id="lb-close"><i class="fas fa-times"></i></button>
        <button class="lb-btn" id="lb-prev"><i class="fas fa-chevron-left"></i></button>
        <button class="lb-btn" id="lb-next"><i class="fas fa-chevron-right"></i></button>
        <div id="lb-box">
            <div id="lb-media"></div>
            <div id="lb-info">
                <span id="lb-cat"></span>
                <div id="lb-title"></div>
                <div id="lb-date"><i class="fas fa-calendar-alt me-1" style="color:var(--blue)"></i> <span
                        id="lb-date-txt"></span></div>
                <div id="lb-desc"></div>
            </div>
        </div>
    </div>

    <!-- Javascript -->
    <script src="assets/plugins/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <?php include 'includes/script.php' ?>

    <script>
        (() => {
            /* ── filter ── */
            const pills = [...document.querySelectorAll('.f-pill')];
            const cards = [...document.querySelectorAll('.g-card')];
            const emptyEl = document.getElementById('emptyState');
            const countEl = document.getElementById('visibleCount');

            const getVisible = () => cards.filter(c => c.style.display !== 'none');

            pills.forEach(pill => {
                pill.addEventListener('click', () => {
                    pills.forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    const f = pill.dataset.filter;
                    let n = 0;
                    cards.forEach(c => {
                        const show = f === 'all' || c.dataset.category === f;
                        c.style.display = show ? '' : 'none';
                        if (show) n++;
                    });
                    countEl.textContent = n;
                    emptyEl.style.display = n ? 'none' : 'block';
                });
            });

            /* ── lightbox ── */
            const backdrop = document.getElementById('lb-backdrop');
            let cur = 0;

            function populate(card) {
                const color = card.dataset.color || '';
                const icon = card.dataset.icon || 'fas fa-image';
                document.getElementById('lb-media').innerHTML =
                    `<div class="lb-icon-area ${color}"><i class="${icon}"></i></div>`;
                document.getElementById('lb-cat').textContent = card.dataset.catLabel || '';
                document.getElementById('lb-title').textContent = card.dataset.title || '';
                document.getElementById('lb-date-txt').textContent = card.dataset.date || '';
                document.getElementById('lb-desc').textContent = card.dataset.desc || '';
            }

            function open(idx) {
                const list = getVisible();
                if (!list[idx]) return;
                cur = idx;
                populate(list[idx]);
                backdrop.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function close() {
                backdrop.classList.remove('open');
                document.body.style.overflow = '';
            }

            cards.forEach(c => c.addEventListener('click', () => {
                const list = getVisible();
                open(list.indexOf(c));
            }));

            document.getElementById('lb-close').addEventListener('click', close);
            backdrop.addEventListener('click', e => { if (e.target === backdrop) close(); });

            document.getElementById('lb-prev').addEventListener('click', () => {
                const list = getVisible();
                open((cur - 1 + list.length) % list.length);
            });
            document.getElementById('lb-next').addEventListener('click', () => {
                const list = getVisible();
                open((cur + 1) % list.length);
            });

            document.addEventListener('keydown', e => {
                if (!backdrop.classList.contains('open')) return;
                if (e.key === 'Escape') close();
                if (e.key === 'ArrowLeft') document.getElementById('lb-prev').click();
                if (e.key === 'ArrowRight') document.getElementById('lb-next').click();
            });
        })();
    </script>
</body>

</html>