#!/usr/bin/env python3
import sys

with open(r'c:\xampp\htdocs\psico_guia_uptp\resources\views\agenda\index.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Search for the broken block
start_index = -1
end_index = -1

for i, line in enumerate(lines):
    if '@else' in line and i > 330:
        if '<div class="h-10 flex items-center justify-center">' in lines[i+1]:
            start_index = i
            break

if start_index != -1:
    # Find the end (the <script> tag)
    for i in range(start_index, len(lines)):
        if '<script>' in lines[i]:
            end_index = i
            break

if start_index != -1 and end_index != -1:
    new_content = [
        '                                                                @else\n',
        '                                                                    <div class="h-10 flex items-center justify-center">\n',
        '                                                                        <div class="w-1 h-1 bg-slate-100 rounded-full"></div>\n',
        '                                                                    </div>\n',
        '                                                                @endif\n',
        '                                                            </td>\n',
        '                                                        @endforeach\n',
        '                                                    </tr>\n',
        '                                                @endforeach\n',
        '                                            </tbody>\n',
        '                                        </table>\n',
        '                                    </div>\n',
        '                                @else\n',
        '                                    <div class="mt-6 min-h-[400px] bg-white rounded-[32px] border-2 border-dashed border-slate-100 p-12 flex flex-col items-center justify-center text-center">\n',
        '                                        <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-3xl flex items-center justify-center mb-6">\n',
        '                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>\n',
        '                                        </div>\n',
        '                                        <h3 class="text-xl font-black text-slate-800 mb-2">Sin Horarios Activos</h3>\n',
        '                                        <p class="text-slate-400 text-sm max-w-xs mx-auto">Gestiona tus grupos de horarios para comenzar a agendar citas en esta semana.</p>\n',
        '                                    </div>\n',
        '                                @endif\n',
        '                            @endif\n',
        '                        </section>\n',
        '                    </div>\n',
        '                </div>\n',
        '            </div>\n',
        '        </div>\n',
        '    </div>\n',
        '\n'
    ]
    
    final_lines = lines[:start_index] + new_content + lines[end_index:]
    
    with open(r'c:\xampp\htdocs\psico_guia_uptp\resources\views\agenda\index.blade.php', 'w', encoding='utf-8') as f:
        f.writelines(final_lines)
    print("File fixed successfully")
else:
    print(f"Could not find broken block. start={start_index}, end={end_index}")
