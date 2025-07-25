import { Users, Target, Heart, Globe } from 'lucide-react';

const About = () => {
  const values = [
    {
      icon: Heart,
      title: 'Customers First',
      description: 'Every product we create is designed with patient safety and comfort as our top priority.'
    },
    {
      icon: Target,
      title: 'Ethical Practices',
      description: 'We strive for perfection in every aspect of our manufacturing and service delivery.'
    },
    {
      icon: Users,
      title: 'Honest Communication',
      description: 'Working closely with healthcare professionals to develop innovative solutions.'
    },
    {
      icon: Globe,
      title: 'Respect Employees',
      description: 'Making quality healthcare accessible to communities worldwide.'
    },
    {
      icon: Globe,
      title: 'Environment Care',
      description: 'Making quality healthcare accessible to communities worldwide.'
    }
  ];

  const timeline = [
    {
      year: '1995',
      title: 'Company Founded',
      description: 'PolyMedicure was established with a vision to revolutionize medical device manufacturing.'
    },
    {
      year: '2001',
      title: 'FDA Approval',
      description: 'Received our first FDA approval for surgical instruments, marking a major milestone.'
    },
    {
      year: '2008',
      title: 'International Expansion',
      description: 'Expanded operations to serve markets in Europe and Asia-Pacific regions.'
    },
    {
      year: '2015',
      title: 'R&D Center',
      description: 'Opened our state-of-the-art research and development facility.'
    },
    {
      year: '2024',
      title: 'Leading Innovation',
      description: 'Continues to lead the industry with cutting-edge medical device solutions.'
    }
  ];

  return (
    <div className="">
      {/* Hero Section */}
      <section style={{ backgroundColor: '#309ed9' }} className="text-white py-20">
  <div className="flex flex-col lg:flex-row max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 items-center gap-12">
    {/* Text Section */}
    <div className="w-full lg:w-1/2 text-left">
      <h1 className="text-4xl md:text-5xl font-bold mb-6">About Eversure</h1>
      <p className="text-xl" style={{ color: '#f0f9ff' }}>
        Eversure is the medical device brand of Pune's Rathigroup, manufacturing a wide range of disposable devices from a certified, world-class local facility. Their product lines include items for Infusion Therapy, Anesthesia, and Urology, all produced using advanced manufacturing and sterilization. The brand is committed to high standards of quality, innovation, and reliability to ensure patient care.
      </p>
    </div>

    {/* Image Section */}
    <div className="w-full lg:w-1/2 flex justify-center">
      <img
        className="w-full max-w-md rounded-full shadow-xl"
        src="/heroimages/about_section.jpeg"
        alt="about-main"
      />
    </div>
  </div>
</section>

      {/* Values Section */}
      <section className="py-16 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">Our Values</h2>
            <p className="text-lg text-gray-600">
              The principles that guide our decisions and shape our company culture.
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {values.map((value, index) => (
              <div key={index} className="text-center p-6 bg-white rounded-lg shadow-sm">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4" style={{backgroundColor: '#f0f9ff'}}>
                  <value.icon className="h-8 w-8" style={{color: '#309ed9'}} />
                </div>
                <h3 className="text-xl font-semibold text-gray-900 mb-2">{value.title}</h3>
                {/* <p className="text-gray-600">{value.description}</p> */}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Timeline */}
      <section className="">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">Our Journey</h2>
            <p className="text-lg text-gray-600">
              Key milestones that have shaped our company's growth and success.
            </p>
          </div>
          <div className="relative">
            <div className="absolute left-1/2 transform -translate-x-1/2 h-full w-0.5 bg-teal-200"></div>
            <div className="space-y-12">
              {timeline.map((item, index) => (
                <div key={index} className={`relative flex items-center ${index % 2 === 0 ? 'justify-start' : 'justify-end'}`}>
                  <div className={`w-1/2 ${index % 2 === 0 ? 'pr-8 text-right' : 'pl-8 text-left'}`}>
                    <div className="bg-white p-6 rounded-lg shadow-sm">
                      <div className="text-teal-600 font-bold text-xl mb-2">{item.year}</div>
                      <h3 className="text-lg font-semibold text-gray-900 mb-2">{item.title}</h3>
                      <p className="text-gray-600">{item.description}</p>
                    </div>
                  </div>
                  <div className="absolute left-1/2 transform -translate-x-1/2 w-4 h-4 bg-teal-600 rounded-full border-4 border-white"></div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};

export default About;

