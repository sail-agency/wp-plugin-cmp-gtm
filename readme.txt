=== Sail CMP & GTM ===
Contributors: sail
Tags: cookies, samtykke, consent mode, gdpr, google tag manager
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.0.2
License: Proprietary

Samtykkebanner for informasjonskapsler med Google Consent Mode v2 og GDPR-vennlig lasting av Google Tag Manager, driftet av Sail.

== Description ==

Pluginen legger Sail CMP-scriptet for nettstedet først i `<head>`, slik at Google Consent Mode står på «denied» før noen tagger lastes. Banneret, tekstene, kategoriene og cookielisten styres av Sail. Den laster også Google Tag Manager i tråd med samtykket.

* Under Innstillinger → Sail CMP velges CMP-leverandør (Sail CMP eller Cookie Information), site-ID for Sail CMP og GTM-ID. Flere GTM-ID-er skilles med komma.
* Kortkoden `[sail_cmp_declaration]` viser cookieerklæringen på personvernsiden.
* Kortkoden `[sail_cmp_settings_link]` gir en lenke som åpner samtykkeinnstillingene igjen. I menyer kan du bruke en egendefinert lenke til `#sail-cmp-open`.
* Script som setter informasjonskapsler, merkes med `type="text/plain" data-sail-category="analytics"` (eller functional/marketing) og kjører først etter samtykke.

Oppdateringer kommer fra Sail (consent.sail.no), ikke fra wordpress.org.

== Installation ==

1. Last opp zip-filen under Utvidelser → Legg til ny → Last opp utvidelse, og aktiver den.
2. Gå til Innstillinger → Sail CMP, velg Sail CMP som CMP-leverandør og fyll inn site-ID-en du har fått fra Sail.
3. Fyll inn GTM-ID hvis Google Tag Manager skal lastes av pluginen.
4. Fjern eventuelle gamle samtykkebannere (Cookie Information, Cookiebot o.l.).


== Changelog ==

= 2.0.2 =
* Forsinket lasting av GTM støtter Sail CMP: GTM lastes først når besøkeren har samtykket til valgt kategori.

= 2.0.1 =
* Site-ID overføres automatisk.
* Standard samtykke (Consent Mode) skrives ikke av pluginen når Sail CMP er valgt, siden Sail CMP setter det selv.
* Hjelpetekst for andre script på innstillingssiden, og innstillingene fjernes når pluginen slettes.

= 2.0.0 =
* Valg av CMP-leverandør (Sail CMP eller Cookie Information).
* Flere GTM-ID-er kan oppgis, skilt med komma.

= 1.0.0 =
* Første versjon av Sail CMP-pluginen: site-ID-felt, script først i <head>, kortkoder for cookieerklæring og innstillingslenke, oppdateringer fra Sail.
