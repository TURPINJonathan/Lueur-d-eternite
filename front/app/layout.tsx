import type { Metadata } from 'next';
import { Cormorant_Garamond, Inter, Playfair_Display } from 'next/font/google';
import './globals.css';
import '../styles/index.scss';
import { Footer, Header } from '#components';

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-inter',
  display: 'swap',
});

const cormorand = Cormorant_Garamond({
  subsets: ['latin'],
  variable: '--font-cormorant',
  display: 'swap',
});

const playfair = Playfair_Display({
  subsets: ['latin'],
  variable: '--font-playfair',
  display: 'swap',
});

export const metadata: Metadata = {
  title: 'Lueur d\Éternité',
  description:
    'À Caen et ses environs, nous entretenons les sépultures avec soin afin que chaque lieu reste digne, apaisant et fidèle au souvenir qu’il représente.',
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="fr" className={`${cormorand.variable} ${playfair.variable} ${inter.variable}`}>
      <body className="font-body">
        <Header />
        <main id="main-content" className="paper-grain">
          {children}
        </main>
        <Footer />
      </body>
    </html>
  );
}
