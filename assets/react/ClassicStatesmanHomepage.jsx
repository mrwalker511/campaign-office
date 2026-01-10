import React from 'react';
import {
  HeroSection,
  StatsBar,
  CoreValuesGrid,
  CandidateBioSplit,
  PolicyDeepDive,
  VolunteerCTA,
  Testimonials,
  NewsSection,
  DonationBanner,
  Footer
} from './components';

/**
 * Classic Statesman Campaign Homepage
 *
 * A complete homepage design with authoritative, trustworthy styling
 * mixing law firm gravitas with grassroots movement energy.
 */
const ClassicStatesmanHomepage = () => {
  // Event Handlers
  const handlePrimaryCTA = () => {
    console.log('Join the Movement clicked');
    // Navigate to volunteer signup or open modal
    window.location.href = '#volunteer';
  };

  const handleSecondaryCTA = () => {
    console.log('Watch the Video clicked');
    // Open video modal or navigate to video page
  };

  const handleVolunteerSignup = (formData) => {
    console.log('Volunteer signup:', formData);
    // Send to backend/CRM
  };

  const handleDonation = (amount) => {
    console.log('Donation amount:', amount);
    // Redirect to donation processor
  };

  const handleEmailSignup = (email) => {
    console.log('Email signup:', email);
    // Add to email list
  };

  return (
    <div className="classic-statesman-homepage bg-neutral-50">
      {/* Hero Section - Full height with rally background */}
      <HeroSection
        backgroundImage="/assets/images/rally-background.svg"
        logo="Anderson for Senate"
        tagline="For Senate '26"
        headline="Restoring Faith In America"
        primaryCTA="Join the Movement"
        secondaryCTA="Watch the Video"
        onPrimaryClick={handlePrimaryCTA}
        onSecondaryClick={handleSecondaryCTA}
      />

      {/* Stats Bar - Dark navy with credibility metrics */}
      <StatsBar
        stats={[
          { value: '25+', label: 'Years of Service' },
          { value: '98%', label: 'Pro-Constitution' },
          { value: '10,000+', label: 'Volunteers' },
          { value: '$2.5M', label: 'Grassroots Raised' }
        ]}
      />

      {/* Core Values - 3 cards with icons */}
      <CoreValuesGrid />

      {/* Candidate Bio - Split layout with photo */}
      <CandidateBioSplit
        image="/assets/images/candidate-portrait.svg"
        name="John Anderson"
        title="Candidate for U.S. Senate"
        bio={[
          'A fifth-generation American with deep roots in our community, John Anderson has spent the last 25 years fighting for the values that made this country great.',
          'As a former Marine, small business owner, and state legislator, John understands the challenges facing everyday Americans. He\'s not a career politician—he\'s a proven leader who gets results.',
          'John and his wife Sarah have raised three children here, coached Little League, and volunteered at local charities. He knows what matters to families because he lives it every day.'
        ]}
        quote="I'm running for Senate because our country needs leaders who will put principle over politics and people over party."
        quoteAttribution="John Anderson"
      />

      {/* Policy Deep-Dive - Numbered list format */}
      <PolicyDeepDive
        sectionTitle="Policy Platform"
        sectionSubtitle="Economic Recovery Plan"
      />

      {/* Volunteer CTA - Full-width dark section */}
      <VolunteerCTA
        backgroundImage="/assets/images/volunteer-office.svg"
        title="Join Our Movement"
        subtitle="Every volunteer makes a difference"
        onSubmit={handleVolunteerSignup}
      />

      {/* Testimonials - 3-column grid */}
      <Testimonials
        sectionTitle="What People Say"
        sectionSubtitle="Voices from the Community"
      />

      {/* News Section - 3 recent updates */}
      <NewsSection
        sectionTitle="Latest News"
        sectionSubtitle="Campaign Updates"
      />

      {/* Donation Banner - Red background with preset amounts */}
      <DonationBanner
        title="Support Our Campaign"
        subtitle="Your contribution helps us reach more voters and spread our message"
        presetAmounts={[50, 100, 250, 500, 1000]}
        onDonate={handleDonation}
      />

      {/* Footer - Dark navy with navigation and social */}
      <Footer
        logo="ANDERSON 2026"
        tagline="Restoring Faith In America"
        onEmailSignup={handleEmailSignup}
      />
    </div>
  );
};

export default ClassicStatesmanHomepage;
