import type { Metadata } from 'next';
import { createPageMetadata } from '../seo';

export const metadata: Metadata = createPageMetadata({
  title: 'Politique de confidentialité',
  description: "Consultez la politique de confidentialité du site Lueur d'Éternité.",
  path: '/politique-de-confidentialite',
  noIndex: true,
});

export default function PrivacyPolicyPage() {
  return (
    <section className="page-shell page-section">
      <h1 className="text-3xl lg:text-4xl">Politique de confidentialité</h1>
      <p className="mt-4 leading-7">
        La politique de confidentialité détaillée sera ajoutée prochainement. En attendant, aucune donnée personnelle
        n&apos;est cédée à des tiers sans consentement explicite.
      </p>
    </section>
  );
}
