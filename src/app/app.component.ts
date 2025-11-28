import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  currentSlide = 0;
  isMenuOpen = false;
  certificateId = '';
  verificationResult: any = null;

  contactForm = {
    name: '',
    email: '',
    phone: '',
    message: ''
  };

  slides = [
    {
      title: 'Where Skill Meets Passion',
      subtitle: 'And Passion Builds a Future',
      description: 'Empowering you with industry-ready skills in Beauty, Lifestyle & Wellness'
    },
    {
      title: 'Educate. Empower. Elevate.',
      subtitle: 'Your Journey Starts Here',
      description: 'High-quality, practical training designed for today\'s generation'
    },
    {
      title: 'Build Your Creative Identity',
      subtitle: 'Transform Your Passion Into Career',
      description: 'From beauty artistry to holistic wellness, unlock your full potential'
    },
    {
      title: 'Learn From Industry Experts',
      subtitle: 'Real Skills, Real Results',
      description: 'Hands-on training that prepares you for success in the real world'
    }
  ];

  courses = [
    { icon: '💄', title: 'Professional Makeup Artistry', duration: '3 Months', level: 'Beginner to Advanced', category: 'Beauty' },
    { icon: '💇', title: 'Hair Styling & Cutting', duration: '4 Months', level: 'All Levels', category: 'Beauty' },
    { icon: '🌸', title: 'Skin Care & Facial Therapy', duration: '2 Months', level: 'Beginner', category: 'Beauty' },
    { icon: '💅', title: 'Nail Art & Manicure', duration: '6 Weeks', level: 'Beginner', category: 'Beauty' },
    { icon: '✨', title: 'Personal Grooming & Styling', duration: '1 Month', level: 'All Levels', category: 'Lifestyle' },
    { icon: '👔', title: 'Professional Image Consulting', duration: '2 Months', level: 'Intermediate', category: 'Lifestyle' },
    { icon: '🧘', title: 'Yoga & Mindfulness', duration: '3 Months', level: 'Beginner', category: 'Wellness' },
    { icon: '🌿', title: 'Holistic Wellness Therapy', duration: '4 Months', level: 'All Levels', category: 'Wellness' },
    { icon: '💆', title: 'Spa & Body Treatments', duration: '3 Months', level: 'Beginner to Intermediate', category: 'Wellness' }
  ];

  highlights = [
    { icon: '👨‍🏫', title: 'Industry-Expert Trainers', description: 'Learn directly from certified professionals and specialists.' },
    { icon: '📚', title: 'Future-Ready Curriculum', description: 'Updated modules designed around real industry needs.' },
    { icon: '🎯', title: '100% Practical Learning', description: 'Perfect balance of hands-on skills and core knowledge.' },
    { icon: '💼', title: 'Career & Business Support', description: 'Guidance for jobs, freelancing, and entrepreneurship.' },
    { icon: '🏢', title: 'Premium Environment', description: 'Modern setup, high-quality tools, and professional atmosphere.' },
    { icon: '🏆', title: 'Valued Certification', description: 'Credentials recognized across beauty, lifestyle, and wellness.' },
    { icon: '🌟', title: 'Holistic Growth', description: 'Communication, grooming, and professional ethics training.' }
  ];

  careers = {
    beauty: ['Makeup Artist', 'Hair Stylist', 'Skin Care Specialist', 'Salon Professional', 'Beauty Consultant', 'Bridal Specialist'],
    lifestyle: ['Personal Grooming Expert', 'Image Consultant', 'Lifestyle Coach', 'Fashion Stylist', 'Content Creator', 'Etiquette Trainer'],
    wellness: ['Wellness Coach', 'Yoga Instructor', 'Mental Wellness Guide', 'Nutrition Mentor', 'Spa Associate']
  };

  careerSupport = [
    { icon: '📋', title: '100% Placement Assistance', description: 'Access opportunities with leading salons, studios, brands, and wellness centers.' },
    { icon: '🎯', title: 'Career Counselling', description: 'One-on-one sessions to choose the right career direction.' },
    { icon: '📄', title: 'Resume & Portfolio Building', description: 'We help you create a professional identity that stands out.' },
    { icon: '🎤', title: 'Interview Training', description: 'Communication, grooming, confidence, and answer preparation.' },
    { icon: '💡', title: 'Freelancing Mentoring', description: 'Learn how to find clients, build packages, and grow your brand.' },
    { icon: '🎓', title: 'Certification Value', description: 'Industry-recognized certification that adds value to your profile.' }
  ];

  blogs = [
    { category: 'Beauty', title: '10 Essential Makeup Tips for Beginners', excerpt: 'Master the basics of makeup application with these professional tips that will transform your beauty routine...', date: 'Nov 20, 2025', image: 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400' },
    { category: 'Lifestyle', title: 'Building Confidence Through Personal Grooming', excerpt: 'Discover how personal grooming impacts your professional presence and opens doors to new opportunities...', date: 'Nov 18, 2025', image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400' },
    { category: 'Wellness', title: 'Mindfulness Practices for Daily Balance', excerpt: 'Simple mindfulness techniques to incorporate into your routine for better mental and emotional wellbeing...', date: 'Nov 15, 2025', image: 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?w=400' }
  ];

  videos = [
    { category: 'Beauty', title: 'Professional Bridal Makeup Tutorial', duration: '15:30', thumbnail: 'https://images.unsplash.com/photo-1487412912498-0447578fcca8?w=400' },
    { category: 'Lifestyle', title: 'Personal Branding Masterclass', duration: '22:45', thumbnail: 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=400' },
    { category: 'Wellness', title: 'Morning Yoga Routine for Beginners', duration: '18:00', thumbnail: 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=400' }
  ];

  partners = [
    'Lakme Salon', 'VLCC', 'Naturals', 'Jawed Habib', 'Green Trends', 'YLG Salon', 'Bodycraft', 'Richfeel'
  ];

  galleryImages = [
    'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=400',
    'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=400',
    'https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?w=400',
    'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400',
    'https://images.unsplash.com/photo-1519699047748-de8e457a634e?w=400',
    'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=400'
  ];

  constructor() {
    this.startSlideshow();
  }

  startSlideshow() {
    setInterval(() => {
      this.currentSlide = (this.currentSlide + 1) % this.slides.length;
    }, 5000);
  }

  goToSlide(index: number) {
    this.currentSlide = index;
  }

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
  }

  scrollToSection(sectionId: string) {
    const element = document.getElementById(sectionId);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
    this.isMenuOpen = false;
  }

  verifyCertificate() {
    if (this.certificateId.trim()) {
      this.verificationResult = {
        valid: this.certificateId.toLowerCase().startsWith('pop'),
        id: this.certificateId,
        name: 'Sample Student',
        course: 'Professional Makeup Artistry',
        date: 'October 2025'
      };
    }
  }

  submitContact() {
    if (this.contactForm.name && this.contactForm.email && this.contactForm.message) {
      alert('Thank you for your message! We will get back to you soon.');
      this.contactForm = { name: '', email: '', phone: '', message: '' };
    }
  }
}
