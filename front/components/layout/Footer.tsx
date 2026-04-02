'use client';

import { ButtonComponent } from '#ui';
import { Copyright, LinkedinIcon, type LucideIcon } from 'lucide-react';
import Link from 'next/link';
import { sanitizePhoneToHref } from '#lib/phone';
import { SiFacebook, SiInstagram, SiTiktok, SiX, SiYoutube } from '@icons-pack/react-simple-icons';
import type { SiteSettingsPublic } from '#lib/siteSettingsApi';

const QUICK_LINKS = [
  { href: '/services', label: 'Services' },
  { href: '/tarifs', label: 'Tarifs' },
  { href: '/galerie', label: 'Galerie' },
  { href: '/a-propos', label: 'À propos' },
];

interface FooterProps {
  contactPhoneDisplay: string;
  contactEmail: string;
  facebookLink: string;
  instagramLink: string;
  linkedinLink: string;
  xLink: string;
  tiktokLink: string;
  youtubeLink: string;
}

const SOCIAL_ENTRIES: {
  key: keyof Pick<
    SiteSettingsPublic,
    'facebookLink' | 'instagramLink' | 'linkedinLink' | 'xLink' | 'tiktokLink' | 'youtubeLink'
  >;
  Icon: LucideIcon | typeof SiTiktok;
  label: string;
}[] = [
  { key: 'facebookLink', Icon: SiFacebook, label: 'Facebook' },
  { key: 'instagramLink', Icon: SiInstagram, label: 'Instagram' },
  { key: 'linkedinLink', Icon: LinkedinIcon, label: 'LinkedIn' },
  { key: 'xLink', Icon: SiX, label: 'X' },
  { key: 'tiktokLink', Icon: SiTiktok, label: 'TikTok' },
  { key: 'youtubeLink', Icon: SiYoutube, label: 'YouTube' },
];

export default function Footer({
  contactPhoneDisplay,
  contactEmail,
  facebookLink,
  instagramLink,
  linkedinLink,
  xLink,
  tiktokLink,
  youtubeLink,
}: FooterProps) {
  const phoneHref = sanitizePhoneToHref(contactPhoneDisplay);

  const socialByKey: Record<(typeof SOCIAL_ENTRIES)[number]['key'], string> = {
    facebookLink,
    instagramLink,
    linkedinLink,
    xLink,
    tiktokLink,
    youtubeLink,
  };

  const socialItems = SOCIAL_ENTRIES.map(({ key, Icon, label }) => {
    const href = socialByKey[key]?.trim();
    if (!href) {
      return null;
    }
    return (
      <a
        key={key}
        href={href}
        target="_blank"
        rel="nofollow noopener noreferrer"
        aria-label={`${label} — Lueur d’Éternité (nouvel onglet)`}
        className="inline-flex"
      >
        <Icon size={20} className="inline-block opacity-50" aria-hidden />
      </a>
    );
  }).filter(Boolean);

  return (
    <footer className="footer inset-shadow-sm/10 page-shell flex flex-col gap-2 pb-2 !mx-0 !w-full">
      <div className="flex flex-wrap gap-4 pt-8 px-10 lg:px-20">
        <div className="flex-2 flex basis-[300px] flex-col gap-2 items-center sm:items-start justify-start text-center sm:text-start">
          <p className="uppercase text-md font-semibold font-title">Lueur d&apos;Éternité</p>
          <p className="italic font-light">
            Service professionnel d’entretien de sépultures à Caen et alentours. Nous préservons la mémoire de vos
            proches avec respect et délicatesse.
          </p>
        </div>

        <div className="flex-1 basis-[185px] flex flex-col gap-2 items-center justify-start">
          <nav aria-label="Liens rapides" className="flex flex-col gap-2 items-center">
            {QUICK_LINKS.map((link) => (
              <Link key={link.href} href={link.href} className="text-md font-title uppercase">
                {link.label}
              </Link>
            ))}
          </nav>
        </div>

        <div className="flex-1 basis-[185px] flex flex-col items-center justify-center gap-2">
          <ButtonComponent href="/contact" variant="gold" size="smf" outline className="uppercase font-title">
            Contactez-nous
          </ButtonComponent>
          <a href={`mailto:${contactEmail}`} className="font-light text-md" aria-label={`Envoyer un email à ${contactEmail}`}>
            {contactEmail}
          </a>
          <a
            href={`tel:${phoneHref}`}
            className="font-light text-md"
            aria-label={`Appeler le ${contactPhoneDisplay}`}
          >
            {contactPhoneDisplay}
          </a>
          {socialItems.length > 0 ? (
            <div className="social-links flex flex-wrap justify-center gap-4">{socialItems}</div>
          ) : null}
        </div>
      </div>

      <div className="w-full flex flex-col md:flex-row justify-between items-center md:items-start gap-4 pt-2 border-t opacity-30 px-10 lg:px-20">
        <div className="flex-1 flex flex-col gap-1 items-center md:items-start justify-center">
          <div className="flex gap-1  items-center md:items-start justify-center">
            <Link href="/mentions-legales">Mentions légales</Link>&middot;<Link href="/cgv">CGV</Link>
          </div>
          <Link href="/politique-de-confidentialite">Politique de confidentialité</Link>
        </div>

        <div className="flex-1 flex flex-col gap-1 items-center justify-center">
          <span className="flex justify-center items-center">
            <Copyright size={16} className="inline-block" aria-hidden />
            <span>2026 copyright &middot; All rights reserved</span>
          </span>
          <span className="uppercase text-xs italic">Lueur d&apos;éternité</span>
        </div>

        <div className="flex-1 flex flex-col md:gap-1 items-center md:items-end justify-center">
          Réalisation & éco-conception par{' '}
          <a href="https://jonathan-turpin.fr" target="_blank" rel="nofollow noopener noreferrer">
            Jonathan TURPIN
          </a>
        </div>
      </div>
    </footer>
  );
}
