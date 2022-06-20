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
Um dies weiter einzuschränken, können folgende Attribute verwendet werden:
<ul style="list-style-type:square; padding-left:1em">
    <li><code>von</code> beschränkt sich auf die Spiele, bei denen die genannte Mannschaft Dienste erbringen muss.</li>
    <li><code>fuer</code> beschränkt sich auf die Spiele, wo die genannte Mannschaft selber spielt</li>
    <li><code>seit</code> ein Datum, ab dem Spiele angezeigt werden. Wenn nichts angegeben, werden alle Spiele seit <u>gestern</u> angezeigt.</li>
</ul>
Mannschaftsnamen (für <code>von</code> und <code>fuer</code>) müssen den folgenden Konventionen entsprechen:
<ul style="list-style-type:square; padding-left:1em">
    <li>für Senioren in der Form <code>Damen 1</code>,<code>Damen 2</code>,<code>Herren 1</code> usw.. </li>
    <li>für Jugendmannschaften in der Form <code>mB1</code>, <code>wC2</code> usw...</li>
</ul>
Bsp: <code>&lt;dienste von="Damen 2"&gt;&lt;/dienste&gt;</code> oder <code>&lt;dienste fuer="Herren 3" seit=01.01.2022"&gt;&lt;/dienste&gt;</code>

<h2>Regelmäßige nuLiga-Updates</h2>
Unter der URL <code><?php echo get_site_url(); ?>/wp-json/dienste/updateFromNuliga</code> kann man den Import von extern starten.<br>
Der Endpunkt ist öffentlich zugänglich und benötigt keine Authentifizierung