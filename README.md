# RRZE-Synonym
WordPress-Plugin, um Synonyme zu erstellen, von Websites aus dem FAU-Netzwerk zu synchronisieren und mittels Shortcodes ([synonym ...] und [fau_abbr ...]) oder als Gutenberg Editor Block (Synonym oder Abkürzung = Dropdown) einzubinden.

## Allgemeines

Das Plugin kann genutzt werden, um Synonyme zu erstellen und Synonyme von Websites aus dem FAU-Netzwerk zu synchronisieren. Die Ausgabe kann wahlweise als reine Langform oder als Akronym-Tag erfolgen, bei dem die Aussprache der Langform unabhängig von der des Akronyms bei der Erstellung des Synonyms definiert werden kann. Beispiel: <abbr title="Universal Resource Locator" lang="en">URL</abbr> da "Universal Resource Locator" im Gegensatz zu "URL" auf einer deutschsprachigen Website Englisch ausgesprochen wird.

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




