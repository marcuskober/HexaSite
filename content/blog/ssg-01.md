---
layout: article
title: Lasst uns einen Static Site Generator mit PHP und Symfony bauen
date: 2024-09-18
lang: de
---
Als erstes stellt sich natürlich die Frage: Warum noch einen Static Site Generator bauen, es gibt doch schon so viele? 

Die Antwort ist ganz klar: aus Spaß und um etwas zu lernen. Mein Hauptaugenmerk lag dabei an noch mehr Umgang mit Symfony, diesmal aber ein wenig anders, mehr modular und from scratch sozusagen. Großen Wert lege ich dabei auch auf die grundlegende Software-Architektur, vor allem was Konzepte wie _Value Objects_, _Factories_ und _Services_ angeht.

In dieser Artikelserie nehme ich euch von Anfang an mit, angefangen bei den ersten Überlegungen, über die Installation von Symfony bis hin zur Unterstützung von mehreren Sprachen.

Das Aussehen dieser Webseite spiegelt dann auch immer den aktuellen Stand von **HexaSite** (so heißt mein Static Site Generator) wider. Zum Zeitpunkt des Schreibens dieses Artikels sind dies nur reine HTML-Files, ohne CSS und Grafiken.

## Warum ein Static Site Generator?

Zum einfachen Bloggen genügt ein Static Site Generator (SSG) und ich liebe es einfach, in Markdown zu schreiben. So kann ich mich komplett auf das Schreiben konzentrieren, ohne mich um Dinge wie Layout usw. zu kümmern. Es zählt zunächst rein der Inhalt, das Styling übernimmt dann der SSG, bzw. dessen Theme.

Ich habe schon mehrere SSGs verwendet (z.B. Hugo und Jekyll), aber der Umgang damit hat mich nie wirklich überzeugt. 

So entstand die Idee, einfach einmal zu sehen, welchen Aufwand es bedeutet, selbst einen SSG in PHP und Symfony zu erstellen.

## Vorüberlegungen

Mein Plan ist es, einen SSG zu erstellen, der so wenig Konfiguration wie möglich benötigt und mölichst viel über Frontmatter steuern lässt.

Frontmatter ist YAML-Code, der - durch Trennzeichen (`---`) separiert - am Anfang der Markdown-Datei eingebunden wird:

```md
---
title: Das ist ein Test
---
Hier folgt der Markdown-Teil
```

Für den Anfang möchte ich folgende Features etablieren:

- Navigation
- Seiten und Blog-Artikel
- Artikel-Übersichten
- Pagination für Artikel-Übersichten
- Einbinden von Bildern
- Syntax Highlighting
- unterschiedliche Themes
- Mehrsprachigkeit

Mal sehen, wie weit ich komme. :)

Durch einen Symfony-Konsolenbefehl soll die Seite gebaut werden, aber es sollen auch Befehle existieren, um bequem neue Seiten und Blog-Artikel anzulegen.

## Eigenes Repository

Es existieren zwei Repositories für HexaSite:

- [HexaSite](https://github.com/marcuskober/HexaSite)
- [HexaSite Learning](https://github.com/marcuskober/HexaSite-Learning)

Das erste (HexaSite) ist das eigentliche Repository meines Static Site Generators und enthält auch die Inhalte dieser Website. Das zweite (HexaSite Learning) könnt ihr nutzen, um der Entwicklung zu folgen, die ich in dieser Artikelserie mit euch teile. Dazu gibt es zu jedem Artikel einen eigenen Tag.

## Jetzt aber los! Wir installieren Symfony

Da ich ganz schlank starten und alles nach Bedarf installieren möchte, beginnen wir mit der Basis-Installation von Symfony. Wir nutzen hier generell die [Symfony CLI](https://symfony.com/download).

Die Basis-Version von Symfony installieren wir über die Konsole:

```bash
symfony new hexasite-learning --version="7.1.*"
```

Dieser Befehl legt das Arbeitsverzeichnis `hexasite-learning` und darin die Symfony-Ordner-Struktur an:

```plain
bin
composer.json
composer.lock
config
public
symfony.lock
src
var
vendor
```

_Git-Tag: [article01-s01](https://github.com/marcuskober/HexaSite-Learning/releases/tag/article01-s01)_

## Input- und Output-Ordner

Wir legen zwei leere Ordner an, die künftig die Markdown-Dateien und die Ausgabe-HTML-Dateien enthalten:

- `build` - für die HTML-Dateien
- `content` - für die Markdown-Dateien

In beide Ordner legen wir leere `.gitignore`-Dateien, damit die leeren Ordner auch in das Repository aufgenommen werden. Wobei die Datei im `build`-Ordner nicht wirklich leer ist - wir wollen nicht, dass die generierten HTML-Dateien ins Repository kommen:

```plain
*
!.gitignore
```

_Git-Tag: [article01-s02](https://github.com/marcuskober/HexaSite-Learning/releases/tag/article01-s02)_

## Beginn der Entwicklung mit einem Command

Nun können wir mit der Entwicklung starten. Dies könnt ihr auf verschiedene Wege tun, zum Beispiel über einen [Test-Driven-Ansatz](https://de.wikipedia.org/wiki/Testgetriebene_Entwicklung). Dies ist aber nicht, worum es hier gehen soll. Wir wollen uns hier immer von einem Prototypen zum nächsten hangeln und dazwischen vernünftige Refactorings durchführen. Wir kümmern uns also zunächst darum, dass unser Code läuft und das gewünschte Ergebnis produziert und in einem direkt folgenden Refactoring kümmern wir uns um eine gute Architektur.

**Fortsetzung folgt.**
