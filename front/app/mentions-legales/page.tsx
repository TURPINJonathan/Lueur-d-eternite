import type { Metadata } from 'next';
import Image from 'next/image';
import { CheckCircle } from 'lucide-react';
import { createPageMetadata } from '../seo';

export const metadata: Metadata = createPageMetadata({
  title: 'Mentions légales',
  description: "Consultez les mentions légales du site Lueur d'Éternité.",
  path: '/mentions-legales',
});

export default function LegalNoticesPage() {
  return (
    <section className="page-shell page-section min-h-[68svh] relative overflow-hidden">
      <div
        aria-hidden="true"
        className="pointer-events-none absolute inset-0 -z-10 flex items-start justify-center translate-y-10"
      >
        <div className="relative h-full w-full">
          <Image
            src="/assets/logo_line.webp"
            alt=""
            fill
            sizes="100vw"
            className="object-contain opacity-10"
            priority={false}
          />
        </div>
      </div>

      <div className="relative z-10 max-w-[920px] mx-auto">
        <h1 className="text-3xl lg:text-4xl">Mentions légales</h1>

        <div className="mt-8 space-y-10">
          <section>
            <h2 className="text-2xl font-semibold">Éditeur du site</h2>
            <p className="leading-7 mt-3">Le présent site internet est édité par :</p>
            <ul className="leading-7 mt-3">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Nom : Émilie SIMON</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Statut : Entrepreneur individuel</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">Adresse : 49 rue de Condé, 14220 Thury-Harcourt-le-Hom, France</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">SIREN : 848 739 546</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">SIRET : 848 739 546 00036</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">TVA : TVA non applicable, article 293B du CGI</span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">
                  Téléphone :{' '}
                  <a className="underline underline-offset-4" href="tel:+33625295952">
                    06 25 29 59 52
                  </a>
                </span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">
                  Email :{' '}
                  <a className="underline underline-offset-4" href="mailto:contact@lueur-eternite.fr">
                    contact@lueur-eternite.fr
                  </a>
                </span>
              </li>
            </ul>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Le site est exploité à l’adresse</h2>
            <p className="leading-7 mt-3">
              <a
                className="underline underline-offset-4"
                href="https://lueur-eternite.fr"
                target="_blank"
                rel="noreferrer"
              >
                https://lueur-eternite.fr
              </a>
            </p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Directeur de la publication</h2>
            <p className="leading-7 mt-3">Directeur de la publication :</p>
            <p className="leading-7 mt-2">Émilie SIMON</p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Hébergement</h2>
            <p className="leading-7 mt-3">Le site est hébergé par :</p>
            <div className="leading-7 mt-3 flex flex-col">
              <span className="leading-6">OVHcloud</span>
              <span className="leading-6">2 rue Kellermann</span>
              <span className="leading-6">59100 Roubaix – France</span>
            </div>
            <p className="leading-7 mt-2">
              <a className="underline underline-offset-4" href="https://www.ovh.com" target="_blank" rel="noreferrer">
                https://www.ovh.com
              </a>
            </p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Accès au site</h2>
            <p className="leading-7 mt-3">
              Le site est accessible gratuitement à tout utilisateur disposant d’un accès à internet.
            </p>
            <p className="leading-7 mt-2">
              L’accès peut être suspendu ou interrompu pour maintenance, mise à jour ou pour toute cause technique
              indépendante de la volonté de l’éditeur.
            </p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Objet du site</h2>
            <p className="leading-7 mt-3">
              Le site a pour objet de présenter les prestations d’entretien et de nettoyage de sépultures proposées par
              l’éditeur, ainsi que de permettre la prise de contact en vue de l’établissement d’un devis.
            </p>
            <p className="leading-7 mt-2">
              Les tarifs affichés sur le site correspondent aux prestations standard présentées. Ils peuvent être
              ajustés en fonction de la situation concrète et des contraintes d’intervention (état de la sépulture,
              accessibilité, distance, règles du cimetière, conditions techniques ou météorologiques, demandes
              spécifiques).
            </p>
            <p className="leading-7 mt-2">Le prix définitif est confirmé par devis avant toute commande.</p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Responsabilité</h2>
            <p className="leading-7 mt-3">
              L’utilisateur est informé que l’accès et l’utilisation du site peuvent être soumis à des aléas techniques.
            </p>
            <p className="leading-7 mt-2">
              Les informations présentées sur le site sont fournies à titre informatif et peuvent évoluer.
            </p>
            <p className="leading-7 mt-2">
              Les visuels et photographies sont non contractuels. Le résultat des prestations peut varier selon l’état
              initial, les matériaux, l’environnement et les contraintes d’intervention.
            </p>
            <p className="leading-7 mt-2">
              Dans la mesure autorisée par la loi, l’éditeur ne pourra être tenu responsable que des dommages directs
              résultant d’une faute prouvée.
            </p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Propriété intellectuelle</h2>
            <p className="leading-7 mt-3">
              L’ensemble des contenus du site est protégé par le droit de la propriété intellectuelle.
            </p>
            <p className="leading-7 mt-2">
              Toute reproduction, représentation ou exploitation sans autorisation est interdite.
            </p>
            <p className="leading-7 mt-2">Les contenus tiers restent la propriété de leurs auteurs respectifs.</p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Données personnelles</h2>
            <p className="leading-7 mt-3">
              Les données personnelles sont traitées conformément à la politique de confidentialité accessible sur le
              site.
            </p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Droit applicable</h2>
            <p className="leading-7 mt-3">Le site est soumis au droit français.</p>
          </section>

          <section>
            <h2 className="text-2xl font-semibold">Contact</h2>
            <ul className="leading-7 mt-3">
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">
                  Email :{' '}
                  <a className="underline underline-offset-4" href="mailto:contact@lueur-eternite.fr">
                    contact@lueur-eternite.fr
                  </a>
                </span>
              </li>
              <li className="flex justify-start items-center mb-1 gap-2">
                <CheckCircle className="h-5 w-5" />
                <span className="leading-6">
                  Téléphone :{' '}
                  <a className="underline underline-offset-4" href="tel:+33625295952">
                    06 25 29 59 52
                  </a>
                </span>
              </li>
            </ul>
          </section>
        </div>
      </div>
    </section>
  );
}
