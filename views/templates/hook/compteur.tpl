{if isset($objectif['somme']) || isset($objectif['appels'])}
<div class="compteur">
    <table>
        <tbody class="compteur_{if isset($objectif['class'])}{$objectif['class']}{/if}">
            <tr>
                <td><span id="compteurAppels"></span> <i class="icon-phone"></i></td>
                <td>{if !empty($objectif['pourcentDeObjectif'])}{$objectif['pourcentDeObjectif']}%{/if}</td>
            </tr>
            <tr>
                <td colspan="2">{if isset($objectif['caCoach'])}{displayPrice price=$objectif['caCoach']}{/if}
                    {if isset($objectif['somme'])} / {displayPrice price=$objectif['somme']}{/if}</td>
            </tr>
        </tbody>
    </table>
</div>
{/if}