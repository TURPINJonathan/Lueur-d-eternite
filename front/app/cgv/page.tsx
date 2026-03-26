import type { Metadata } from 'next';
import Image from 'next/image';
import { CheckCircle } from 'lucide-react';
import { createPageMetadata } from '../seo';

export const metadata: Metadata = createPageMetadata({
  title: 'Conditions Générales de Vente',
  description: "Consultez les Conditions Générales de Vente de Lueur d'Éternité.",
  path: '/cgv',
});

export default function CGVPage() {
  const listItemTextClass = 'leading-6';

  return (
    <section className="page-shell page-section min-h-[68svh] relative overflow-hidden">
      <div
        aria-hidden="true"
        className="pointer-events-none absolute inset-0 -z-10 flex items-start justify-center translate-y-10"
      >
        <div className="relative h-full w-full">
          <Image
            src="/assets/logo_line.webp"
            alt="Logo en fond de section"
            fill
            sizes="100vw"
            className="object-contain opacity-10"
            priority={false}
          />
        </div>
      </div>

      <div className="relative z-10 max-w-[920px] mx-auto">
        <h1 className="text-3xl lg:text-4xl">Conditions Générales de Vente (CGV)</h1>

        <div className="mt-8 space-y-10">
          <section id="formation-du-contrat">
            <h2 className="text-2xl font-semibold">Formation du contrat</h2>
            <p className="leading-7 mt-3">Le contrat est conclu uniquement après acceptation expresse du devis.</p>
            <p className="leading-7 mt-2">Le cas échéant, il devient définitif après réception de l’acompte.</p>
          </section>

          <section id="prix">
            <h2 className="text-2xl font-semibold">Prix</h2>
            <p className="leading-7 mt-3">Les tarifs du site sont indicatifs.</p>
            <p className="leading-7 mt-2">Le prix final est celui du devis.</p>
          </section>

          <section id="paiement">
            <h2 className="text-2xl font-semibold">Paiement</h2>
            <ul className="leading-7 mt-3">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>Paiement : acompte possible + solde après intervention</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>Moyens : virement, espèces, chèque</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>En cas d’impayé : suspension possible</span>
              </li>
            </ul>
          </section>

          <section id="zone-d-intervention">
            <h2 className="text-2xl font-semibold">Zone d’intervention</h2>
            <p className="leading-7 mt-3">Prestations limitées à 15 km autour de Caen.</p>
          </section>

          <section id="retractation">
            <h2 className="text-2xl font-semibold">Rétractation</h2>
            <p className="leading-7 mt-3">Délai légal de 14 jours applicable.</p>
            <p className="leading-7 mt-3">Si le client demande une intervention immédiate :</p>
            <ul className="leading-7 mt-2">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>il accepte le démarrage avant 14 jours</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>il peut perdre son droit de rétractation</span>
              </li>
            </ul>
          </section>

          <section id="obligation-de-moyens">
            <h2 className="text-2xl font-semibold">Obligation de moyens</h2>
            <p className="leading-7 mt-3">Le prestataire n’est tenu qu’à une obligation de moyens.</p>
            <p className="leading-7 mt-3">Le résultat dépend :</p>
            <ul className="leading-7 mt-2">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>état du monument</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>matériaux</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>météo</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>contraintes techniques</span>
              </li>
            </ul>
          </section>

          <section id="satisfaction">
            <h2 className="text-2xl font-semibold">Satisfaction (IMPORTANT)</h2>
            <p className="leading-7 mt-3">
              La satisfaction garantie constitue un geste commercial éventuel, non une garantie de résultat.
            </p>
          </section>

          <section id="photos">
            <h2 className="text-2xl font-semibold">Photos</h2>
            <p className="leading-7 mt-3">Le client autorise l’usage sauf refus explicite.</p>
            <p className="leading-7 mt-2">Possibilité de retrait sur demande.</p>
          </section>

          <section id="obligations-du-client">
            <h2 className="text-2xl font-semibold">Obligations du client</h2>
            <p className="leading-7 mt-3">Le client garantit :</p>
            <ul className="leading-7 mt-2">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>être autorisé à intervenir sur la sépulture</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>fournir des informations exactes</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>respecter les règles du cimetière</span>
              </li>
            </ul>
          </section>

          <section id="annulation">
            <h2 className="text-2xl font-semibold">Annulation</h2>
            <p className="leading-7 mt-3">Gratuit jusqu’à 72h</p>
            <p className="leading-7 mt-2">Après : frais ou acompte conservé</p>
          </section>

          <section id="reclamations">
            <h2 className="text-2xl font-semibold">Réclamations</h2>
            <p className="leading-7 mt-3">Délai : 14 jours après intervention</p>
          </section>

          <section id="responsabilite">
            <h2 className="text-2xl font-semibold">Responsabilité</h2>
            <ul className="leading-7 mt-3">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>Limitée aux dommages directs.</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className={listItemTextClass}>Pas de dommages indirects.</span>
              </li>
            </ul>
          </section>

          <section id="mediation">
            <h2 className="text-2xl font-semibold">Médiation</h2>
            <p className="leading-7 mt-3">CM2C</p>
            <p className="leading-7 mt-2">
              <a className="underline underline-offset-4" href="https://www.cm2c.net" target="_blank" rel="noreferrer">
                https://www.cm2c.net
              </a>
            </p>
          </section>
        </div>
      </div>
    </section>
  );
}
