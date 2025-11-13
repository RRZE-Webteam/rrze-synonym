
[![Aktuelle Version](https://img.shields.io/github/package-json/v/rrze-webteam/rrze-synonym/main?label=Version)](https://github.com/RRZE-Webteam/rrze-synonym)
[![Release Version](https://img.shields.io/github/v/release/rrze-webteam/rrze-synonym?label=Release+Version)](https://github.com/rrze-webteam/rrze-synonym/releases/)
[![GitHub License](https://img.shields.io/github/license/rrze-webteam/rrze-synonym)](https://github.com/RRZE-Webteam/rrze-synonym)
[![GitHub issues](https://img.shields.io/github/issues/RRZE-Webteam/rrze-synonym)](https://github.com/RRZE-Webteam/rrze-synonym/issues)

**⚠️ Important Notice (Code Freeze)**
> 
> Starting with version **3.0.3**, this plugin is no longer maintained.  
> All functionality has been merged into the new WordPress plugin **RRZE-Answers**.  
> Please submit any new issues related to RRZE-Synonym v3.0.3 in the [RRZE-Answers repository](https://github.com/RRZE-Webteam/rrze-answers/issues/)).
>


# RRZE Synonym

WordPress-Plugin, um Synonyme (Abkürzungen) zu erstellen, von Websites aus dem FAU-Netzwerk zu synchronisieren und mittels Shortcodes ([synonym ...] und [fau_abbr ...]) oder als Gutenberg Editor Block (Synonym oder Abkürzung = Dropdown) einzubinden.

## Contributors

* RRZE-Webteam, http://www.rrze.fau.de

## Copyright

GNU General Public License (GPL) Version 2


## Documentation

Komplette Dokumentation unter: https://www.wp.rrze.fau.de

Das Plugin kann genutzt werden, um Synonyme zu erstellen und Synonyme von Websites aus dem FAU-Netzwerk zu synchronisieren. Die Ausgabe kann wahlweise als reine Langform oder als Akronym-Tag erfolgen, bei dem die Aussprache der Langform unabhängig von der des Akronyms bei der Erstellung des Synonyms definiert werden kann. Beispiel: <abbr title="Universal Resource Locator" lang="en">URL</abbr> da "Universal Resource Locator" im Gegensatz zu "URL" auf einer deutschsprachigen Website Englisch ausgesprochen wird. Eine Übersicht über alle Synonyme finden Sie unter Ihre-Website/synonym mit Angabe der Aussprache bei den Einträgen, die sich von der Sprache Ihrer Website unterscheiden.

## Verwendung der Shortcodes

```html
[synonym id="123"] 
[synonym slug="bildungsministerium"] 
[synonym] 
[fau_abbr id="987"] 
[fau_abbr slug="url"] 
[fau_abbr] 
```


## Erklärungen und Werte zu den Attributen des Shortcodes

id : mit diesem Attribut erfolgt die Ausgabe eines Synonyms. Sie finden die ID in der rechten Spalte unter "Synonyme"->"Alle Synonyme" sowie in der Informationsbox "Einfügen in Seiten und Beiträgen" bei jedem Synonym im Bearbeitungsmodus.

slug : mit diesem Attribut erfolgt die Ausgabe eines Synonyms. Sie finden den slug als letzten Teil des Permalinks, sowie in der Informationsbox "Einfügen in Seiten und Beiträgen" bei jedem Synonym im Bearbeitungsmodus.

Rufen Sie die Shortcodes [synonym] oder [fau_abbr] ohne Attribute auf, wird eine Liste aller Synonyme ausgeben.  


## Synonyme von anderer Domain

Hierzu muss die gewünschte Domain über den Menüpunkt "Einstellungen" -> "RRZE Synonym" -> Tab "Domains" hinzugefügt werden.
Das Synchronisieren kann über den Menüpunkt "Einstellungen" -> "RRZE Synonym" -> Tab "Synchonisierung" vorgenommen werden.
Synchronisierte Synonyme können nun wie selbst erstellte Synonyme mit dem Shortcode oder im Gutenberg Editor als Block ausgegeben werden.




