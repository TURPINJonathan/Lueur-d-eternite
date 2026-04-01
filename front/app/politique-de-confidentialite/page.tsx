import type { Metadata } from 'next';
import Image from 'next/image';
import { CheckCircle } from 'lucide-react';
import { createPageMetadata } from '../seo';
import { getSiteSettings } from '#lib';

export const metadata: Metadata = createPageMetadata({
  title: 'Politique de confidentialité',
  description: "Consultez la politique de confidentialité du site Lueur d'Éternité.",
  path: '/politique-de-confidentialite',
});

export default async function PrivacyPolicyPage() {
  const siteSettings = await getSiteSettings(300);
  const policyUpdatedAt = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' }).format(new Date(siteSettings.updatedAt));

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
        <h1 className="text-3xl lg:text-4xl">Politique de confidentialité</h1>

        <div className="mt-8 space-y-10">
          <section>
            <h2 className="text-2xl font-semibold">Responsable du traitement</h2>
            <div className="leading-6">{siteSettings.legalEntityName}</div>
            <div className="leading-6">{siteSettings.legalAddress}</div>
            <a className="underline underline-offset-4" href={`mailto:${siteSettings.contactEmail}`}>
              {siteSettings.contactEmail}
            </a>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Données collectées</h2>
            <ul className="leading-7 mt-3">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Nom / prénom</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Email</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Téléphone</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Message</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Données techniques (IP, logs)</span>
              </li>
            </ul>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Finalités</h2>
            <ul className="leading-7 mt-3">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Répondre aux demandes</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Établir un devis</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Exécuter les prestations</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Sécurité du site</span>
              </li>
            </ul>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Base légale</h2>
            <ul className="leading-7 mt-3">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Mesures précontractuelles</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Exécution du contrat</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Intérêt légitime</span>
              </li>
            </ul>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Formulaire</h2>
            <p className="leading-7 mt-3">
              Les données envoyées via le formulaire sont utilisées uniquement pour répondre à la demande.
            </p>
            <p className="leading-7 mt-2">Aucune base de données commerciale n’est constituée.</p>
            <p className="leading-7 mt-2">Les utilisateurs doivent transmettre uniquement les données nécessaires.</p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Photos</h2>
            <p className="leading-7 mt-3">Les photos peuvent être utilisées :</p>
            <ul className="leading-7 mt-2">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">pour la prestation</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">pour preuve d’intervention</span>
              </li>
            </ul>
            <p className="leading-7 mt-3">Utilisation marketing uniquement :</p>
            <ul className="leading-7 mt-2">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">avec accord du client</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">ou anonymisation suffisante</span>
              </li>
            </ul>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Durée de conservation</h2>
            <ul className="leading-7 mt-3">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Prospects : 3 ans max</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Clients : durée légale</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Logs : durée technique raisonnable</span>
              </li>
            </ul>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Destinataires</h2>
            <ul className="leading-7 mt-3">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Émilie SIMON</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">OVH (hébergement + email)</span>
              </li>
            </ul>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Transferts</h2>
            <p className="leading-7 mt-3">Pas de transfert volontaire hors UE.</p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Droits</h2>
            <p className="leading-7 mt-3">Accès, rectification, suppression :</p>
            <p className="leading-7 mt-2">
              <a className="underline underline-offset-4" href={`mailto:${siteSettings.contactEmail}`}>
                {siteSettings.contactEmail}
              </a>
            </p>
            <p className="leading-7 mt-2">Réclamation : CNIL</p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Cookies</h2>
            <p className="leading-7 mt-3">Aucun cookie de tracking utilisé.</p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Dernière mise à jour</h2>
            <p className="leading-7 mt-3">{policyUpdatedAt}</p>
          </section>
        </div>
      </div>
    </section>
  );
}
