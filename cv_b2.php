<?php

$c1b2 = <<<'HTML'
                            <td class="p-4 text-center space-y-1">
                                @if($cob->estado === 'pendiente')
                                    <form action="{{ route('cobranzas.pagar', $cob->id_cobranza) }}" method="POST" onsubmit="return confirm('¿Confirma registrar el cobro de la mora por S/ {{ $cob->monto_mora }}?');">
                                        @csrf
                                        <input type="hidden" name="monto" value="{{ $cob->monto_mora }}">
                                        <input type="hidden" name="medio_pago" value="efectivo">
                                        <button type="submit" class="w-full py-1.5 px-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-lg text-[11px] transition shadow">
                                            💵 Cobrar Mora
                                        </button>
                                    </form>
                                @endif

                                @if($cob->alquiler->estado !== 'devuelto')
                                    <form action="{{ route('cobranzas.recuperar', $cob->id_alquiler) }}" method="POST" onsubmit="return confirm('¿Confirma registrar la recuperación de la herramienta en campo?');">
                                        @csrf
                                        <input type="hidden" name="estado_fisico" value="Bueno - Recuperado por cobranzas">
                                        <input type="hidden" name="observaciones" value="Herramienta recuperada de obra por el gestor de cobranzas.">
                                        <button type="submit" class="w-full py-1.5 px-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-[11px] transition shadow">
                                            🛠️ Recuperar Equipo
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 italic">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-3xl mb-2 block"></i>
                                ¡Excelente! No hay registros de clientes morosos o cobro de mora pendientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
HTML;

file_put_contents(__DIR__ . '/resources/views/cobranzas/index.blade.php', $c1b2, FILE_APPEND);
echo "cv_b2 done\n";
