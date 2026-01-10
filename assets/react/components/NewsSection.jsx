import React from 'react';
import { motion } from 'framer-motion';
import { useInView } from 'framer-motion';
import { useRef } from 'react';
import { Calendar, ArrowRight } from 'lucide-react';

const NewsCard = ({ date, title, excerpt, link, delay = 0 }) => {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true });

  return (
    <motion.article
      ref={ref}
      initial={{ opacity: 0, y: 30 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay }}
      className="bg-white p-8 border-l-4 border-navy hover:border-gold transition-all duration-300 group"
      data-testid="news-card"
    >
      <div className="flex items-center gap-2 text-gold text-sm font-sans uppercase tracking-wider mb-4">
        <Calendar size={16} />
        <time dateTime={date}>{new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</time>
      </div>
      <h3 className="font-display text-navy text-xl md:text-2xl font-bold mb-4 group-hover:text-gold transition-colors duration-300">
        {title}
      </h3>
      <p className="font-sans text-neutral-700 text-base leading-relaxed mb-6">
        {excerpt}
      </p>
      <a
        href={link}
        data-testid="news-link"
        className="inline-flex items-center gap-2 font-sans text-navy text-sm font-semibold uppercase tracking-wider hover:text-gold transition-colors duration-200"
      >
        Read More
        <ArrowRight size={16} className="group-hover:translate-x-1 transition-transform duration-200" />
      </a>
    </motion.article>
  );
};

const NewsSection = ({
  sectionTitle = 'Latest News',
  sectionSubtitle = 'Campaign Updates',
  news = [
    {
      date: '2026-01-08',
      title: 'Anderson Campaign Announces Statewide Town Hall Tour',
      excerpt: 'Join us for a series of town halls across the state where John will hear directly from voters about the issues that matter most to you and your family.',
      link: '#'
    },
    {
      date: '2026-01-05',
      title: 'Grassroots Donors Propel Campaign Past $2.5M Milestone',
      excerpt: 'Thanks to over 10,000 individual contributors, our campaign has reached a major fundraising milestone—proving that people-powered campaigns can compete.',
      link: '#'
    },
    {
      date: '2026-01-02',
      title: 'Veterans Groups Endorse Anderson for Senate',
      excerpt: 'Three major veterans organizations have announced their endorsement, citing John\'s commitment to supporting those who served and protecting VA benefits.',
      link: '#'
    }
  ],
  viewAllLink = '#'
}) => {
  return (
    <section className="bg-white py-20 md:py-28">
      <div className="container mx-auto px-6 max-w-7xl">
        {/* Section Header */}
        <div className="text-center mb-16">
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="text-gold uppercase text-sm font-sans font-semibold tracking-[0.3em] mb-4"
          >
            {sectionTitle}
          </motion.p>
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.1 }}
            className="font-display text-navy text-4xl md:text-5xl lg:text-6xl font-bold"
          >
            {sectionSubtitle}
          </motion.h2>
        </div>

        {/* News Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
          {news.map((item, index) => (
            <NewsCard
              key={index}
              date={item.date}
              title={item.title}
              excerpt={item.excerpt}
              link={item.link}
              delay={index * 0.2}
            />
          ))}
        </div>

        {/* View All Link */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.6 }}
          className="text-center"
        >
          <a
            href={viewAllLink}
            data-testid="view-all-news"
            className="inline-flex items-center gap-2 bg-navy text-white px-10 py-4 font-sans font-semibold text-base tracking-[0.15em] uppercase hover:bg-navy-700 transition-colors duration-200"
          >
            View All News
            <ArrowRight size={20} />
          </a>
        </motion.div>
      </div>
    </section>
  );
};

export default NewsSection;
