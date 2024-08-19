<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
                <img src="https://rhac.org.kh/wp-content/uploads/2023/03/Logo-RHAC.png" class="logo" alt="RHAC Logo">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
