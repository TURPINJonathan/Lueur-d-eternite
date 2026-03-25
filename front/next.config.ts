import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
  async rewrites() {
    // If you define NEXT_PUBLIC_BACK_BASE_URL (e.g. "http://localhost:8000"),
    // we proxy /api/* to the Symfony back so Next/Image can use relative URLs.
    const backBaseUrl = process.env.NEXT_PUBLIC_BACK_BASE_URL;
    if (!backBaseUrl) return [];

    const trimmed = backBaseUrl.replace(/\/+$/, '');
    return [
      {
        source: '/api/:path*',
        destination: `${trimmed}/api/:path*`,
      },
    ];
  },
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'images.pexels.com',
      },
    ],
    formats: ['image/avif', 'image/webp'],
    minimumCacheTTL: 60 * 60 * 24 * 7,
  },
};

export default nextConfig;
