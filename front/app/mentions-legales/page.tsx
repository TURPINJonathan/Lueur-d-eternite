import type { Metadata } from 'next';
import { createPageMetadata } from '../seo';

export const metadata: Metadata = createPageMetadata({
  title: 'Mentions légales',
  description: "Consultez les mentions légales du site Lueur d'Éternité.",
  path: '/mentions-legales',
  noIndex: true,
});

export default function LegalNoticesPage() {
  return (
    <section className="page-shell page-section">
      <h1 className="text-3xl lg:text-4xl">Mentions légales</h1>
      <p className="mt-4 leading-7">
        Les informations légales détaillées seront publiées prochainement. Pour toute demande urgente, vous pouvez nous
        contacter via la page contact.
      </p>
    </section>
  );
}
