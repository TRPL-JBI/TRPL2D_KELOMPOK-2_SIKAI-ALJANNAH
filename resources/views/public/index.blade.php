<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RKM Al-Jannah</title>
    @include('components.favicon')
    
    <!-- Google Fonts: Space Grotesk (Navbar) & Poppins (Content) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Navbar Component Styles -->
    @vite('resources/css/components/navbar.css')

    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            font-style: normal;
            background-color: #f5f5f5;
        }

        /* Hero Section */
        .hero {
            position: relative;
            height: 100dvh;
            width: 100%;
            overflow: hidden;
            background: linear-gradient(180deg, #f5f5f5 0%, #e8e8e8 100%);
        }

        /* Decorative Images */
        .hero-decor-left {
            position: absolute;
            top: -5%;
            left: -5%;
            width: auto;
            height: 80%;
            max-height: 700px;
            object-fit: contain;
            z-index: 1;
            pointer-events: none;
        }

        .hero-decor-right {
            position: absolute;
            top: 0;
            right: 0;
            width: auto;
            height: 55%;
            max-height: 462px;
            object-fit: contain;
            z-index: 1;
            pointer-events: none;
        }

        /* Hero Content */
        .hero-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 2rem;
        }

        /* Hero Logo Container */
        .hero-logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            margin-top: -10%; /* Move slightly up */
        }

        .hero-logo {
            width: auto;
            height: auto;
            max-width: 367px;
            max-height: 310px;
            object-fit: contain;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #16423C;
            text-align: center;
            letter-spacing: -0.02em;
            font-family: "Poppins", sans-serif;
        }

        .hero-subtitle {
            font-size: 1rem;
            font-weight: 400;
            font-style: italic;
            color: #6B7280;
            text-align: center;
            margin-top: 0.5rem;
            font-family: "Poppins", sans-serif;
            max-width: 500px;
            line-height: 1.6;
        }

        /* Hero Responsive */
        @media (max-width: 768px) {
            /* Hero Mobile */
            .hero-decor-left {
                left: -15%;
                height: 60%;
                max-height: 450px;
            }

            .hero-decor-right {
                top: 0;
                right: 0;
                height: 40%;
                max-height: 280px;
            }

            .hero-logo-container {
                margin-top: -5%;
            }

            .hero-logo {
                max-width: 250px;
                max-height: 210px;
            }

            .hero-title {
                font-size: 1.75rem;
            }

            .hero-subtitle {
                font-size: 0.875rem;
            }
        }

        @media (max-width: 480px) {
            .hero-decor-left {
                left: -25%;
                height: 30%;
            }

            .hero-decor-right {
                right: -25%;
                height: 25%;
            }

            .hero-logo {
                max-width: 200px;
                max-height: 170px;
            }

            .hero-title {
                font-size: 1.5rem;
            }

            .hero-subtitle {
                font-size: 0.8rem;
            }
        }

        /* Bottom Ornament */
        .hero-bottom-ornament {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: none;
            height: auto;
            min-height: 150px;
            object-fit: cover;
            z-index: 1;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .hero-bottom-ornament {
                min-height: 100px;
            }
        }

        @media (max-width: 480px) {
            .hero-bottom-ornament {
                min-height: 80px;
            }
        }

        /* Entrance Animations */
        @keyframes slideInFromTop {
            from {
                opacity: 0;
                transform: translateY(-100px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInFromLeft {
            from {
                opacity: 0;
                transform: translateX(-100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInFromRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInFromBottom {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(100px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Animation Classes */
        .hero-logo {
            animation: slideInFromTop 1s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
        }

        .hero-title {
            animation: fadeIn 1s cubic-bezier(0.4, 0, 0.2, 1) 0.3s forwards;
            opacity: 0;
        }

        .hero-subtitle {
            animation: fadeIn 1s cubic-bezier(0.4, 0, 0.2, 1) 0.5s forwards;
            opacity: 0;
        }

        .hero-decor-left {
            animation: slideInFromLeft 1.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
        }

        .hero-decor-right {
            animation: slideInFromRight 1.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
        }

        .hero-bottom-ornament {
            animation: slideInFromBottom 1s cubic-bezier(0.4, 0, 0.2, 1) 0.2s forwards;
            opacity: 0;
        }

        /* About Section (Apa itu RKM Al-Jannah) */
        .about-section {
            padding: 4rem 2rem;
            background-color: #f5f5f5;
        }

        .about-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .about-box {
            background: linear-gradient(135deg, #1E5A52 0%, #98CBBE 100%);
            border-radius: 16px;
            padding: 2.5rem 3rem;
            box-shadow: 0 10px 40px rgba(30, 90, 82, 0.3);
        }

        .about-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .about-content {
            font-family: "Poppins", sans-serif;
            font-size: 1.125rem;
            font-weight: 600;
            color: #ffffff;
            text-align: center;
            line-height: 1.8;
            max-width: 900px;
            margin: 0 auto;
        }

        .about-footer {
            font-family: "Poppins", sans-serif;
            font-size: 1.0625rem;
            font-weight: 700;
            color: #396E56;
            text-align: center;
            margin-top: 2rem;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .about-section {
                padding: 3rem 1.5rem;
            }

            .about-box {
                padding: 2rem 1.5rem;
            }

            .about-title {
                font-size: 1.5rem;
            }

            .about-content {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .about-title {
                font-size: 1.25rem;
            }

            .about-content {
                font-size: 0.95rem;
            }
        }

        /* Vision & Mission Section */
        .vision-mission-section {
            padding: 4rem 2rem;
            background-color: #f5f5f5;
        }

        .vision-mission-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .vm-box {
            background: linear-gradient(135deg, #1E5A52 0%, #98CBBE 100%);
            border-radius: 16px;
            padding: 2rem 2.5rem;
            box-shadow: 0 10px 40px rgba(30, 90, 82, 0.3);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            min-height: 280px;
        }

        .vm-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1.25rem;
            text-align: center;
        }

        .vm-content {
            font-family: "Poppins", sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            line-height: 1.8;
        }

        .vm-content.center {
            text-align: center;
        }

        .vm-content ul {
            list-style: none;
            padding-left: 0;
            margin-top: 0.75rem;
        }

        .vm-content ul li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .vm-content ul li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #ffffff;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .vision-mission-section {
                padding: 3rem 1.5rem;
            }

            .vision-mission-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .vm-box {
                padding: 1.75rem 2rem;
                min-height: auto;
            }

            .vm-title {
                font-size: 1.5rem;
            }

            .vm-content {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 480px) {
            .vm-title {
                font-size: 1.25rem;
            }

            .vm-content {
                font-size: 0.9rem;
            }
        }

        /* Scroll Animation for Vision & Mission */
        .vm-box {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .vm-box.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .vm-title {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .vm-box.animate .vm-title {
            opacity: 1;
            transform: translateY(0);
        }

        .vm-content,
        .vm-content p,
        .vm-content ul,
        .vm-content ul li {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .vm-box.animate .vm-content,
        .vm-box.animate .vm-content p,
        .vm-box.animate .vm-content ul,
        .vm-box.animate .vm-content ul li {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger delays */
        .vm-box:nth-child(1).animate .vm-title {
            transition-delay: 0.1s;
        }

        .vm-box:nth-child(1).animate .vm-content {
            transition-delay: 0.2s;
        }

        .vm-box:nth-child(2).animate .vm-title {
            transition-delay: 0.2s;
        }

        .vm-box:nth-child(2).animate .vm-content {
            transition-delay: 0.3s;
        }

        .vm-content.animate .vm-content p,
        .vm-content.animate .vm-content ul,
        .vm-content.animate .vm-content ul li {
            opacity: 1;
            transform: translateY(0);
        }

        .vm-content p:nth-child(1) {
            transition-delay: 0.1s;
        }

        .vm-content p:nth-child(2) {
            transition-delay: 0.15s;
        }

        .vm-content ul li:nth-child(1) {
            transition-delay: 0.2s;
        }

        .vm-content ul li:nth-child(2) {
            transition-delay: 0.25s;
        }

        .vm-content ul li:nth-child(3) {
            transition-delay: 0.3s;
        }

        .vm-content p:nth-child(5) {
            transition-delay: 0.35s;
        }

        /* News Section (Kanal Berita) */
        .news-section {
            padding: 4rem 2rem;
            background-color: #f5f5f5;
        }

        .news-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }

        /* News Section (Kanal Berita) */
        .news-section {
            padding: 4rem 2rem;
            background: linear-gradient(180deg, #98CBBE 0%, #1E5A52 100%);
            min-height: 400px;
        }

        .news-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .news-header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 2rem;
        }

        .news-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #1E5A52;
            background-color: #ffffff;
            padding: 0.5rem 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .news-content {
            /* Custom Row System */
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            padding-bottom: 1.5rem;
            margin: 0;
        }

        /* Column System - Similar to Bootstrap */
        .news-content .news-col {
            flex: 0 0 calc(33.333333% - 1rem);
            max-width: calc(33.333333% - 1rem);
        }

        /* Responsive Columns */
        @media (max-width: 1024px) {
            .news-content .news-col {
                flex: 0 0 calc(50% - 0.75rem);
                max-width: calc(50% - 0.75rem);
            }
        }

        @media (max-width: 768px) {
            .news-content .news-col {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        /* Hide cards with data-hidden="true" */
        .news-content .news-col[data-hidden="true"] {
            display: none !important;
        }

        .news-footer {
            display: flex;
            justify-content: center;
            padding-top: 1.5rem;
        }

        .btn-see-all {
            font-family: "Poppins", sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            color: #1E5A52;
            background-color: #ffffff;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-see-all:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .news-content {
            /* Placeholder for news cards */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 250px;
            color: rgba(255, 255, 255, 0.8);
            font-family: "Poppins", sans-serif;
            font-size: 1.125rem;
        }

        @media (max-width: 768px) {
            .news-section {
                padding: 3rem 1.5rem;
            }

            .news-wrapper {
                padding: 2rem 1.5rem;
            }

            .news-header {
                top: -2.5rem;
            }

            .news-title {
                font-size: 1.5rem;
                padding: 0.5rem 1.5rem;
            }

            .news-content {
                grid-template-columns: 1fr;
            }

            .btn-see-all {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .news-header {
                top: -2rem;
            }

            .news-title {
                font-size: 1.25rem;
                padding: 0.5rem 1rem;
            }
        }

        /* Contact Section (Hubungi Kami) */
        .contact-section {
            padding: 4rem 2rem;
            background: linear-gradient(180deg, #98CBBE 0%, #1E5A52 100%);
            min-height: 400px;
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-header {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
        }

        .contact-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #1E5A52;
            background-color: #ffffff;
            padding: 0.5rem 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 0;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .contact-card {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(22, 66, 60, 0.3);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(22, 66, 60, 0.5);
        }

        .contact-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #6A9C89 0%, #16423C 100%);
            border-radius: 50%;
            color: #ffffff;
        }

        .contact-card-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #16423C;
            margin-bottom: 1rem;
        }

        .contact-card-text {
            font-family: "Poppins", sans-serif;
            font-size: 1rem;
            font-weight: 400;
            color: #374151;
            line-height: 1.8;
        }

        .contact-card-text a {
            color: #16423C;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .contact-card-text a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        /* Benefits Section (Keuntungan) */
        .benefits-section {
            padding: 4rem 2rem;
            background-color: #ffffff;
            min-height: 400px;
        }

        .benefits-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .benefits-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #1E5A52;
            text-align: center;
            margin-bottom: 3rem;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        .benefits-card {
            background: linear-gradient(135deg, #6A9C89 0%, #16423C 100%);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(22, 66, 60, 0.3);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .benefits-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(22, 66, 60, 0.5);
        }

        .benefits-card-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 70px;
            height: 70px;
            background-color: #ffffff;
            border-radius: 50%;
            color: #16423C;
            margin: 0 auto 0.5rem;
        }

        .benefits-card-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            text-align: center;
        }

        .benefits-card-text {
            font-family: "Poppins", sans-serif;
            font-size: 0.95rem;
            font-weight: 400;
            color: #ffffff;
            line-height: 1.8;
            text-align: center;
            flex: 1;
        }

        /* Member Benefits Section */
        .member-benefits-section {
            padding: 4rem 2rem;
            background-color: #ffffff;
        }

        .member-benefits-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .member-benefits-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #1E5A52;
            text-align: center;
            margin-bottom: 3rem;
        }

        .member-benefits-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        .member-benefits-card {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            border: 2px solid #16423C;
            box-shadow: 0 4px 15px rgba(22, 66, 60, 0.2);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .member-benefits-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(22, 66, 60, 0.4);
        }

        .member-benefits-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            margin-bottom: 1.5rem;
        }

        .member-benefits-card-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.125rem;
            font-weight: 700;
            color: #16423C;
        }

        @media (max-width: 1024px) {
            .member-benefits-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .member-benefits-section {
                padding: 3rem 1.5rem;
            }

            .member-benefits-grid {
                grid-template-columns: 1fr;
            }

            .member-benefits-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .member-benefits-title {
                font-size: 1.25rem;
            }

            .member-benefits-card {
                padding: 1.5rem;
            }

            .member-benefits-image {
                height: 150px;
            }

            .member-benefits-card-title {
                font-size: 1rem;
            }
        }

        @media (max-width: 1024px) {
            .benefits-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .benefits-section {
                padding: 3rem 1.5rem;
            }

            .benefits-grid {
                grid-template-columns: 1fr;
            }

            .benefits-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .benefits-title {
                font-size: 1.25rem;
            }

            .benefits-card {
                padding: 1.5rem;
            }

            .benefits-card-icon {
                width: 60px;
                height: 60px;
            }

            .benefits-card-title {
                font-size: 1.125rem;
            }

            .benefits-card-text {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 1024px) {
            .contact-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .contact-section {
                padding: 3rem 1.5rem;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .contact-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .contact-title {
                font-size: 1.25rem;
            }

            .contact-card {
                padding: 1.5rem;
            }

            .contact-icon {
                width: 60px;
                height: 60px;
            }
        }

        /* Footer */
        .footer {
            background: linear-gradient(180deg, #16423C 0%, #0d2b26 100%);
            padding: 2rem;
            margin-top: 0;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .footer-brand-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }

        .footer-brand-text {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-divider {
            width: 1px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.3);
        }

        .footer-developer-text {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-developer-name {
            font-weight: 600;
            color: #98CBBE;
        }

        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-divider {
                width: 100%;
                height: 1px;
            }

            .footer {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .footer-brand-title {
                font-size: 1.25rem;
            }

            .footer {
                padding: 1rem;
            }
        }
    </style>
    <script>if('serviceWorker'in navigator){navigator.serviceWorker.register('/sw.js')}</script>
</head>
<body>
    @include('components.preloader')
    <!-- Navbar Component -->
    <x-navbar />

    <!-- Hero Section -->
    <section class="hero">
        <!-- Decorative Images -->
        <img src="{{ asset('images/ranting.png') }}" alt="Decorative branch" class="hero-decor-left">
        <img src="{{ asset('images/pohon.png') }}" alt="Decorative tree" class="hero-decor-right">

        <!-- Hero Content -->
        <div class="hero-content">
            <div class="hero-logo-container">
                <img src="{{ asset('images/logo-al-jannah.png') }}" alt="RKM Al-Jannah Logo" class="hero-logo">
                <h1 class="hero-title">{{ $content['hero']['title'] ?? 'RKM Al-Jannah' }}</h1>
                <p class="hero-subtitle">{{ $content['hero']['subtitle'] ?? 'Bersama dalam Kepedulian, Hadir saat Masa Duka dengan Amanah dan Transparan' }}</p>
            </div>
        </div>

        <!-- Bottom Ornament -->
        <img src="{{ asset('images/bottom-ornament.png') }}" alt="Bottom ornament" class="hero-bottom-ornament">
    </section>

    <!-- About Section (Apa itu RKM Al-Jannah) -->
    <section class="about-section" id="tentang-kami">
        <div class="about-container">
            <div class="about-box">
                <h2 class="about-title">{{ $content['about']['title'] ?? 'Apa itu RKM AL JANNAH ?' }}</h2>
                <p class="about-content">
                    {{ $content['about']['content'] ?? 'Rukun Kematian (RKM) AL Jannah adalah bentuk kerjasama antara yayasan Sa\'ad Bin Abi Waqqosh dengan sanggar Ma\'e, sebagai tanda bakti kepada masyarakat dalam memberikan pertolongan kepada anggota Rukun Kematian yang meninggal dunia yang sesuai Sunnah (tata cara yang rosululloh shallallahu Aalaihi wasallam ajarkan).' }}
                </p>
            </div>
            <p class="about-footer">
                {!! $content['about']['footer'] ?? 'Berdiri sejak: 2017<br>Di bawah naungan Yayasan Sa\'ad Bin Abi Waqqash' !!}
            </p>
        </div>
    </section>

    <!-- Vision & Mission Section -->
    <section class="vision-mission-section" id="visi-misi">
        <div class="vision-mission-container">
            <!-- Visi Box -->
            <article class="vm-box">
                <h2 class="vm-title">Visi</h2>
                <p class="vm-content center">
                    {{ $content['vision'] ?? 'Rukun kematian yang mampu mengangkat harkat dan martabat keluarga anggotanya yang meninggal dunia.' }}
                </p>
            </article>

            <!-- Misi Box -->
            <article class="vm-box">
                <h2 class="vm-title">Misi</h2>
                <div class="vm-content">
                    @php $missionItems = $content['mission']['items'] ?? []; @endphp
                    @forelse($missionItems as $i => $point)
                        <p>{{ ($i + 1) . '. ' . ($point['text'] ?? '') }}</p>
                        @if(!empty($point['subheading']))
                            <p>{{ $point['subheading'] }}</p>
                        @endif
                        @if(!empty($point['sub_items']))
                            <ul>
                                @foreach($point['sub_items'] as $sub)
                                    <li>{{ $sub }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @empty
                        <p>1. Memberikan pertolongan yang adil dan merata bagi seluruh anggota RKM.</p>
                        <p>Pertolongan yang dimaksud adalah:</p>
                        <ul>
                            <li>Bantuan materi</li>
                            <li>Bantuan Tenaga</li>
                            <li>Bantuan Jasa</li>
                        </ul>
                        <p>2. Membantu masyarakat yang membutuhkan bantuan dalam hal kepengurusan jenazah</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>

    <!-- News Section (Kanal Berita) -->
    <section class="news-section" id="kanal-berita">
        <div class="news-container">
            <div class="news-header">
                <h2 class="news-title">Kanal Berita</h2>
            </div>
            <div class="news-content" id="newsContent">
                @forelse($posts as $index => $post)
                    @php
        $imageUrl = $post->getThumbnailUrl() ?? 'https://via.placeholder.com/600x400?text=No+Image';
                        $tags = $post->category ? [$post->category->name] : ['Umum'];
                        $isHidden = $index >= 6;
                    @endphp
                    <div class="news-col" {{ $isHidden ? 'data-hidden="true"' : '' }}>
                        <x-news-card 
                            image="{{ $imageUrl }}"
                            :tags="$tags"
                            title="{{ $post->title }}"
                            url="{{ route('post.show', $post->slug) }}"
                        />
                    </div>
                @empty
                    <div class="news-col" style="flex: 0 0 100%; max-width: 100%; text-align: center; color: rgba(255,255,255,0.8); padding: 2rem;">
                        Belum ada berita.
                    </div>
                @endforelse
                </div>
                <div class="news-footer">
                    <a href="#semua-berita" class="btn-see-all">Lihat Selengkapnya</a>
                </div>
        </div>
    </section>

    <!-- Benefits Section (Layanan & Keuntungan) -->
    <section class="benefits-section" id="layanan-keuntungan">
        <div class="benefits-container">
            @php $services = $content['services'] ?? []; @endphp
            <h2 class="benefits-title">{{ $services['title'] ?? 'Layanan Kami' }}</h2>
            <div class="benefits-grid">
                @php
                    $defaultServices = [
                        ['title' => 'Perawatan Jenazah', 'desc' => 'Melaksanakan proses pemulasaraan jenazah sesuai syariat Islam, meliputi pemandian dan perawatan jenazah oleh petugas yang telah ditetapkan.', 'image' => '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>'],
                        ['title' => 'Pengafanan', 'desc' => 'Menyediakan 1 (satu) set perlengkapan kain kafan lengkap beserta kebutuhan lainnya, serta pelaksanaan pengafanan sesuai tuntunan syariat.', 'image' => '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line><line x1="8.12" y1="8.12" x2="12" y2="12"></line></svg>'],
                        ['title' => 'Ambulance', 'desc' => 'Menyediakan layanan mobil ambulance untuk pengantaran jenazah ke tempat pemakaman dengan pengaturan jadwal dan rute yang terkoordinasi.', 'image' => '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>'],
                        ['title' => 'Pemakaman', 'desc' => 'Mengatur dan mendampingi pelaksanaan sholat jenazah serta proses pemakaman hingga selesai sesuai ketentuan yang berlaku.', 'image' => '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M5 21V7l8-4 8 4v14"></path><path d="M17 21v-8.5a1.5 1.5 0 0 0-3 0V21"></path></svg>'],
                    ];
                    $items = $services['items'] ?? $defaultServices;
                @endphp
                @foreach($items as $i => $svc)
                    @php
                        $img = $svc['image'] ?? $defaultServices[$i]['image'] ?? '';
                    @endphp
                    <div class="benefits-card">
                        <div class="benefits-card-icon">
                            @if(is_numeric($img))
                                <img src="{{ route('media.download', (int)$img) }}" alt="{{ $svc['title'] ?? '' }}" style="width:36px;height:36px;object-fit:contain;">
                            @elseif(str_starts_with($img, '<svg'))
                                {!! $img !!}
                            @elseif($img)
                                <img src="{{ asset($img) }}" alt="{{ $svc['title'] ?? '' }}">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                            @endif
                        </div>
                        <h3 class="benefits-card-title">{{ $svc['title'] ?? $defaultServices[$i]['title'] ?? '' }}</h3>
                        <p class="benefits-card-text">{{ $svc['description'] ?? $svc['desc'] ?? $defaultServices[$i]['desc'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Member Benefits Section (Keuntungan Anggota) -->
    <section class="member-benefits-section" id="keuntungan-anggota">
        <div class="member-benefits-container">
            @php $memberBenefits = $content['member_benefits'] ?? []; @endphp
            <h2 class="member-benefits-title">{{ $memberBenefits['title'] ?? 'Keuntungan Anggota' }}</h2>
            <div class="member-benefits-grid">
                @php
                    $defaultBenefits = [
                        ['title' => 'Santunan', 'image' => 'santunan.png'],
                        ['title' => 'Paket Pengurusan Jenazah', 'image' => 'paket-pengurusan-jenazah.png'],
                        ['title' => 'Hak Suara dalam Rapat', 'image' => 'hak-suara.png'],
                        ['title' => 'Pelayanan Setara', 'image' => 'pelayanan-setara.png'],
                    ];
                    $items = $memberBenefits['items'] ?? $defaultBenefits;
                @endphp
                @foreach($items as $i => $benefit)
                    @php
                        $img = $benefit['image'] ?? $defaultBenefits[$i]['image'] ?? '';
                        $ttl = $benefit['title'] ?? $defaultBenefits[$i]['title'] ?? '';
                    @endphp
                    <div class="member-benefits-card">
                        @if(is_numeric($img))
                            <img src="{{ route('media.download', (int)$img) }}" alt="{{ $ttl }}" class="member-benefits-image" style="object-fit:contain;">
                        @elseif(str_starts_with($img, '<svg'))
                            <div class="member-benefits-image" style="display:flex;align-items:center;justify-content:center;">{!! $img !!}</div>
                        @elseif($img)
                            @php $imgPath = str_contains($img, '/') ? $img : 'images/keuntungan/'.$img; @endphp
                            <img src="{{ asset($imgPath) }}" alt="{{ $ttl }}" class="member-benefits-image">
                        @else
                            <div class="member-benefits-image" style="display:flex;align-items:center;justify-content:center;background:#f0f7f5;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#16423c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                            </div>
                        @endif
                        <h3 class="member-benefits-card-title">{{ $ttl }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Contact Section (Hubungi Kami) -->
    <section class="contact-section" id="hubungi-kami">
        <div class="contact-container">
            <div class="contact-header">
                @php $contact = $content['contact'] ?? []; @endphp
                <h2 class="contact-title">{{ $contact['title'] ?? 'Hubungi Kami' }}</h2>
            </div>
            <div class="contact-grid">
                <div class="contact-card">
                    <div class="contact-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h3 class="contact-card-title">Alamat</h3>
                    <p class="contact-card-text">
                        {!! $contact['address'] ?? 'Yayasan Sa\'ad Bin Abi Waqqosh<br>Sanggar Ma\'e<br>Indonesia' !!}
                    </p>
                </div>

                <div class="contact-card">
                    <div class="contact-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <h3 class="contact-card-title">Email</h3>
                    <p class="contact-card-text">
                        <a href="mailto:{{ $contact['email'] ?? 'info@aljannah.org' }}">{{ $contact['email'] ?? 'info@aljannah.org' }}</a>
                    </p>
                </div>

                <div class="contact-card">
                    <div class="contact-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <h3 class="contact-card-title">Telepon</h3>
                    <p class="contact-card-text">
                        <a href="tel:{{ $contact['phone'] ?? '+6281234567890' }}">{{ $contact['phone'] ?? '+62 812-3456-7890' }}</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Component -->
    <x-footer />

    <!-- Script untuk Scroll Animation & Service Worker -->
    <script>
        // Scroll Animation for Vision & Mission Section
        const visionMissionSection = document.querySelector('.vision-mission-section');
        const vmBoxes = document.querySelectorAll('.vm-box');

        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.35
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    vmBoxes.forEach((box, index) => {
                        setTimeout(() => {
                            box.classList.add('animate');
                        }, index * 100);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        if (visionMissionSection) {
            observer.observe(visionMissionSection);
        }

        // News Cards Pagination - Row System (3 rows per click)
        document.addEventListener('DOMContentLoaded', function() {
            const newsContent = document.getElementById('newsContent');
            const btnSeeAll = document.querySelector('.btn-see-all');
            
            if (!newsContent || !btnSeeAll) return;
            
            const cardsPerRow = 3;
            const rowsPerClick = 3; // Show 3 rows (9 cards) per click
            const initialRows = 2; // Start with 2 rows (6 cards)
            
            let currentRows = initialRows;
            
            function updateVisibility() {
                const allCols = newsContent.querySelectorAll('.news-col');
                const totalCols = allCols.length;
                const maxVisible = currentRows * cardsPerRow;
                
                let visibleCount = 0;
                
                allCols.forEach((col, index) => {
                    if (index < maxVisible) {
                        col.setAttribute('data-hidden', 'false');
                        visibleCount++;
                    } else {
                        col.setAttribute('data-hidden', 'true');
                    }
                });
                
                // Update button text
                if (visibleCount < totalCols) {
                    btnSeeAll.textContent = 'Lihat Selengkapnya';
                } else {
                    btnSeeAll.textContent = 'Tutup';
                }
            }
            
            btnSeeAll.addEventListener('click', function(e) {
                e.preventDefault();
                
                const allCols = newsContent.querySelectorAll('.news-col');
                const totalCols = allCols.length;
                const currentVisibleCount = currentRows * cardsPerRow;
                
                // Check if we should close
                if (currentVisibleCount >= totalCols) {
                    // Close - back to initial 2 rows
                    currentRows = initialRows;
                } else {
                    // Show more - add 3 rows (9 cards)
                    currentRows += rowsPerClick;
                    
                    // Don't exceed total cards
                    if (currentRows * cardsPerRow > totalCols) {
                        currentRows = Math.ceil(totalCols / cardsPerRow);
                    }
                }
                
                updateVisibility();
                
                // Smooth scroll if showing more
                if (currentRows * cardsPerRow <= totalCols) {
                    setTimeout(() => {
                        newsContent.scrollIntoView({ behavior: 'smooth', block: 'end' });
                    }, 100);
                }
            });
            
            // Initialize
            updateVisibility();
        });

    </script>
</body>
</html>
