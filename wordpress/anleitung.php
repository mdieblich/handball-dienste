<h1>Dienste-Plugin</h1>
<p>Hallo!</p>
<p>Mit diesem Plugin soll die Verwaltung der Dienste eines Handballvereins vereinfacht werden.</p>
<p>Wir gehen das Schritt-für-Schritt durch:</p>

<h2>Einstellungen</h2>
In den Wordpress-Einstellungen (<i>Seite "Allgemein"</i>) findet sich ein Abschnitt <i>Dienste Einstellungen</i>. Dort sollte eingestellt werden:
<div style="padding-left:2em">
    <p>
        <b>Vereinsname</b><br>
        Der Vereinsname wird benötigt, um den Verein in nuLiga zu identifizieren. Beim Import wird diese Einstellung daher verwendet. 
        Stellt ihr z.B. hier den Vereinsnamen <i>"HC Doofenhausen"</i> ein, so werden alle Mannschaften <i>"HC Doofenhausen"</i>, <i>"HC Doofenhausen II"</i> usw. erkannt.
    </p>
    <p>
        <b>Bot-SMTP, Bot-Email, Bot-Passwort</b><br>
        Hier werden die Email-Zugangsdaten für einen Email-Bot hinterlegt.<br>
        Immer, wenn die Spiele von nuLiga neu importiert werden, so sendet dieser Bot Emails raus. 
        Diese Emails enthalten pro Mannschaft eine Zusammenstellung aller geänderten Spiele, bei denen diese Mannschaft einen Dienst ausüben muss.
    </p>
</div>

<h2>Dienste darstellen</h2>
In einem Beitrag oder einer Seite kann der Tag <code>&lt;dienste&gt;&lt;/dienste&gt;</code> verwendet werden. Dies wird mit einer Tabelle ersetzt, welche alle Dienste darstellt.<br>
Um dies weiter einzuschränken, können die Attribute <code>von</code> und <code>fuer</code> benutzt werden. Dabei erwarten diese den Mannschaftsnamen in der Form 
<code>Damen 1</code>,<code>Damen 2</code>,<code>Herren 1</code> usw.. <br>
Bsp: <code>&lt;dienste von="Damen 2"&gt;&lt;/dienste&gt;</code> oder <code>&lt;dienste fuer="Herren 3"&gt;&lt;/dienste&gt;</code>