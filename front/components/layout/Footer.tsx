'use client';

import { ButtonComponent } from '#ui';
import { Copyright, Facebook, Instagram } from 'lucide-react';

export default function Footer() {
  return (
    <footer className="footer inset-shadow-sm/10 flex flex-col gap-2 pt-8 pb-2 px-10 lg:px-20 ">
      <div className="flex flex-wrap gap-4">
        <div className="flex-2 flex basis-[300px] flex-col gap-2 items-center sm:items-start justify-start text-center sm:text-start">
          <p className="uppercase text-md font-semibold font-title">Lueur d&apos;Éternité</p>
          <p className="italic font-light">
            Service professionnel d’entretien de sépultures à Caen et alentours. Nous préservons la mémoire de vos
            proches avec respect et délicatesse.
          </p>
        </div>

        <div className="flex-1 basis-[185px] flex flex-col gap-2 items-center justify-start">
          <p className="uppercase text-md font-title">Services</p>
          <p className="uppercase text-md font-title">Tarifs</p>
          <p className="uppercase text-md font-title">Galerie</p>
          <p className="uppercase text-md font-title">A propos</p>
        </div>

        <div className="flex-1 basis-[185px] flex flex-col items-center justify-center gap-2">
          <ButtonComponent variant="gold" size="smf" outline className="uppercase font-title">
            Contactez-nous
          </ButtonComponent>
          <p className="font-light text-md">contact@lueur-eternite.com</p>
          <p className="font-light text-md">06 25 29 59 52</p>
          <div className="social-links flex gap-4">
            <Facebook size={20} className="inline-block opacity-50" aria-hidden />
            <Instagram size={20} className="inline-block opacity-50" aria-hidden />
          </div>
        </div>
      </div>

      <div className="w-full flex flex-col md:flex-row justify-between items-center md:items-start gap-4 pt-2 border-t opacity-30">
        <div className="flex-1 flex flex-col gap-1 items-center md:items-start justify-center">
          <p>Mentions légales</p>
          <p>Politique de confidentialité</p>
        </div>

        <div className="flex-1 flex flex-col gap-1 items-center justify-center">
          <span className="flex justify-center items-center">
            <Copyright size={16} className="inline-block" aria-hidden />
            <span>2026 copyright - All rights reserved</span>
          </span>
          <span className="uppercase text-xs italic">Lueur d&apos;éternité</span>
        </div>

        <div className="flex-1 flex flex-col md:gap-1 items-center md:items-end justify-center">
          Réalisation & éco-conception par{' '}
          <a href="https://jonathan-turpin.frcom" target="_blank" rel="nofollow">
            Jonathan TURPIN
          </a>
        </div>
      </div>
    </footer>
  );
}
