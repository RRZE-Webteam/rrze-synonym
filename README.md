# rrze-synonym
WordPress-Plugin: Shortcode zur Einbindung von eigenen Synonymen sowie von Synonymen aus dem FAU-Netzwerk 

## Allgemeins

Die Möglichkeit eigene Synonyme zu definieren ist nun in ein eigenes Plugin ausgelagert worden.
Weiterhin können Synonyme wie gewohnt im Backend unter dem Menüpunkt Synonyme angelegt werden.
Darüber hinaus können nun auch Synonyme von anderen Domains eingebunden werden.

## Verwendung des Shortcodes (wie bisher)

```html
[synonym id="2215687"]
[synonym slug="fau"]
```

## Erweiterung des Shortcodes (Synonyme von anderen Domains)

Um diesen Dienst zu verwenden muss die gewünschte Domain hinzugefügt werden.
Danach wird im Backend automatisch eine Liste der vorhandenen Synonyme dieser Domain erstellt. __(Menüpunkt Zeige Server Synonyme)__

Der Shortcode wurde um den Paramter rest und domain erweitert.<br/>
Die Domain-Id sehen Sie unter dem Menüpunkt - __Alle Domains__ - bei der jeweiligen Domain.

Den Slug und die jeweilige Id für das Synonym können Sie der Liste unter dem Menüpunkt - __Zeige Server Synonyme__ - entnehmen.

```html
[synonym rest="1" domain="1" id="10"]
[synonym rest="1" domain="2" slug="site2"]
```
