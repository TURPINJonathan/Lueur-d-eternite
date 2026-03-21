'use client';

import Image from 'next/image';
import Link from 'next/link';
import { useEffect, useState } from 'react';
import Logo from '../../public/assets/logo_full.webp';
import { usePathname } from 'next/navigation';

const NAV_LINKS = [
  { href: '/', label: 'Accueil' },
  { href: '/services', label: 'Services' },
  { href: '/tarifs', label: 'Tarifs' },
  { href: '/galerie', label: 'Galerie' },
  { href: '/a-propos', label: 'À propos' },
  { href: '/contact', label: 'Contact' },
];

export default function Header() {
  const [menuOpen, setMenuOpen] = useState(false);
  const pathname = usePathname();

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setMenuOpen(false);
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, []);

  const close = () => setMenuOpen(false);

  return (
    <>
      {menuOpen && <div className="fixed inset-0 z-[1020] lg:hidden" aria-hidden="true" onClick={close} />}

      <div className="sticky top-0 z-[1030] h-[var(--nav-height)]">
        <header>
          <div className="relative flex w-full items-center justify-end pb-5">
            <Link
              href="/"
              onClick={close}
              className="absolute left-0 pt-5 top-0 translate-y-4 z-10 font-display flex h-full items-center justify-center tracking-tight"
            >
              <Image
                src={Logo}
                alt="Lueur d'Éternité — Retour à l’accueil"
                width={100}
                priority
                className="h-auto max-h-[inherit] , filter: 'drop-shadow(0 10px 30px rgba(63, 92, 110, 0.3))'"
              />
            </Link>

            <nav
              className="
                hidden lg:flex relative isolate h-full items-center gap-7
                pt-5 pl-30 pr-7 pb-5
                rounded-bl-[30px]
                overflow-hidden
                backdrop-blur-sm
                [mask-image:linear-gradient(to_right,transparent_0%,black_12%,black_100%)]
                [-webkit-mask-image:linear-gradient(to_right,transparent_0%,black_12%,black_100%)]

                before:pointer-events-none before:absolute before:inset-0 before:-z-10
                before:bg-[linear-gradient(90deg,rgba(28,28,28,0)_0%,rgba(28,28,28,0.18)_10%,rgba(28,28,28,0.38)_50%,rgba(28,28,28,0.65)_100%)]

                after:pointer-events-none after:absolute after:left-0 after:bottom-0 after:h-px after:w-full after:-z-10
                after:bg-[linear-gradient(90deg,rgba(255,246,213,0)_0%,rgba(241,210,122,0.9)_18%,rgba(212,175,55,1)_58%,rgba(168,132,47,0.9)_100%)]
                after:shadow-[0_0_6px_rgba(212,175,55,0.28)]
              "
              aria-label="Navigation principale"
            >
              {NAV_LINKS.map(({ href, label }) => {
                const isActive = href === '/' ? pathname === '/' : pathname === href || pathname.startsWith(`${href}/`);

                return (
                  <Link
                    key={href}
                    href={href}
                    className={[
                      'text-sm',
                      isActive ? 'text-gold-luxe' : 'text-[var(--color-white)] text-gold-luxe-hover',
                    ].join(' ')}
                  >
                    {label}
                  </Link>
                );
              })}
            </nav>
            <button
              type="button"
              className="burger-gold mt-5 flex lg:hidden"
              onClick={() => setMenuOpen((v) => !v)}
              aria-expanded={menuOpen}
              aria-controls="mobile-nav"
              aria-label={menuOpen ? 'Fermer le menu' : 'Ouvrir le menu'}
            >
              <span
                className={`burger-line-gold origin-center transition-all duration-200 ${
                  menuOpen ? 'translate-y-[7px] rotate-45' : ''
                }`}
              />
              <span
                className={`burger-line-gold transition-all duration-200 ${menuOpen ? 'scale-x-0 opacity-0' : ''}`}
              />
              <span
                className={`burger-line-gold origin-center transition-all duration-200 ${
                  menuOpen ? '-translate-y-[7px] -rotate-45' : ''
                }`}
              />
            </button>
          </div>
        </header>

        <div
          id="mobile-nav"
          aria-label="Menu mobile"
          className={[
            'mobile-nav-panel  shadow-2xl backdrop-blur-lg absolute right-0 top-full w-1/2 overflow-hidden lg:hidden',
            menuOpen ? 'max-h-[500px] opacity-100 translate-y-0' : 'max-h-0 opacity-0 -translate-y-2',
          ].join(' ')}
        >
          <nav className="mobile-nav-list mx-auto flex w-full max-w-6xl flex-col">
            {NAV_LINKS.map(({ href, label }) => {
              const isActive = href === '/' ? pathname === '/' : pathname === href || pathname.startsWith(`${href}/`);

              return (
                <Link
                  key={href}
                  href={href}
                  onClick={close}
                  className={['mobile-nav-link', isActive ? 'mobile-nav-link-active' : 'mobile-nav-link-idle'].join(
                    ' ',
                  )}
                >
                  {label}
                </Link>
              );
            })}
          </nav>
        </div>
      </div>
    </>
  );
}
