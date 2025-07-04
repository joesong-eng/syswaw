<label for="machine_type" class=" block text-sm text-gray-600">{{ __('msg.machine_type') }}</label>
<select id="machine_type" name="machine_type"
    class="w-full mt-1 p-2 border rounded-md focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
    x-model="selectedMachine.machine_type" required>
    <option value="claw">{{ __('msg.claw_machine') }}</option> {{-- <--- 確認 value="claw" --}}
    <option value="normally">{{ __('msg.normally') }}</option>
    <option value="pachinko">{{ __('msg.pachinko') }}</option>
    <option value="claw_machine">{{ __('msg.claw_machine') }}</option>
    <option value="beat_em_up">{{ __('msg.beat_em_up') }}</option>
    <option value="racing_game">{{ __('msg.racing_game') }}</option>
    <option value="light_gun_game">{{ __('msg.light_gun_game') }}</option>
    <option value="dance_game">{{ __('msg.dance_game') }}</option>
    <option value="basketball_game">{{ __('msg.basketball_game') }}</option>
    <option value="air_hockey">{{ __('msg.air_hockey') }}</option>
    <option value="slot_machine">{{ __('msg.slot_machine') }}</option>
    <option value="light_and_sound_game">{{ __('msg.light_and_sound_game') }}</option>
    <option value="labyrinth_game">{{ __('msg.labyrinth_game') }}</option>
    <option value="flight_simulator">{{ __('msg.flight_simulator') }}</option>
    <option value="punching_machine">{{ __('msg.punching_machine') }}</option>
    <option value="water_shooting_game">{{ __('msg.water_shooting_game') }}</option>
    <option value="stacker_machine">{{ __('msg.stacker_machine') }}</option>
    <option value="mini_golf_game">{{ __('msg.mini_golf_game') }}</option>
    <option value="interactive_dance_game">{{ __('msg.interactive_dance_game') }}</option>
    <option value="electronic_shooting_game">{{ __('msg.electronic_shooting_game') }}</option>
    <option value="giant_claw_machine">{{ __('msg.giant_claw_machine') }}</option>
    <option value="arcade_music_game">{{ __('msg.arcade_music_game') }}</option>
</select>
